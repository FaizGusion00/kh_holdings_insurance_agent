"use client";

import React, { createContext, useContext, useEffect, useState, ReactNode, useCallback } from 'react';
import { apiService, User, ApiResponse } from '@/app/services/api';
import { useRouter } from 'next/navigation';

interface LoginResult {
  success: boolean;
  message?: string;
  errors?: Record<string, string[]>;
  user?: User;
  token?: string;
}

interface AuthContextType {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (agentCode: string, password: string) => Promise<LoginResult>;
  logout: () => Promise<void>;
  updateUser: (userData: Partial<User>) => void;
  clearErrors: () => void;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isInitialized, setIsInitialized] = useState(false);
  const router = useRouter();

  // Enhanced authentication check with retry logic
  const checkAuth = useCallback(async (retryCount = 0): Promise<void> => {
    try {
      if (apiService.isAuthenticated()) {
        const response = await apiService.getProfile();
        if (response.success && response.data) {
          setUser(response.data.user);
        } else {
          // Token is invalid, clear it
          apiService.clearToken();
          setUser(null);
        }
      } else {
        setUser(null);
      }
    } catch (error) {
      console.error('Auth check failed:', error);
      
      // Retry logic for network issues
      if (retryCount < 2) {
        console.log(`Retrying auth check (${retryCount + 1}/2)...`);
        setTimeout(() => checkAuth(retryCount + 1), 1000 * (retryCount + 1));
        return;
      }
      
      apiService.clearToken();
      setUser(null);
    } finally {
      setIsLoading(false);
      setIsInitialized(true);
    }
  }, []);

  useEffect(() => {
    checkAuth();
  }, [checkAuth]);

  // Enhanced login with better error handling and user feedback
  const login = async (agentCode: string, password: string): Promise<LoginResult> => {
    try {
      setIsLoading(true);
      
      // Validate input
      if (!agentCode || !password) {
        return { 
          success: false, 
          message: 'Agent code and password are required' 
        };
      }

      // Validate agent code format (allow both AGT12345 and email formats)
      const isValidAgentCode = /^AGT\d{5}$/.test(agentCode);
      const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(agentCode);
      
      if (!isValidAgentCode && !isValidEmail) {
        return { 
          success: false, 
          message: 'Invalid format. Use AGT followed by 5 digits or email address.' 
        };
      }

      const response = await apiService.login(agentCode, password);
      
      if (response.success && response.data) {
        const { user: userData, token } = response.data;
        
        // Set user and token
        setUser(userData);
        
        // Store additional user data if needed
        if (token) {
          apiService.setToken(token);
        }
        
        return { 
          success: true, 
          message: 'Login successful',
          user: userData,
          token
        };
      }
      
      // Handle specific error cases
      if (response.message) {
        return { 
          success: false, 
          message: response.message, 
          errors: response.errors 
        };
      }
      
      return { 
        success: false, 
        message: 'Login failed. Please check your credentials and try again.',
        errors: response.errors 
      };
      
    } catch (error: any) {
      console.error('Login failed:', error);
      
      // Handle network errors
      if (error.name === 'TypeError' && error.message.includes('fetch')) {
        return { 
          success: false, 
          message: 'Network error. Please check your internet connection and try again.' 
        };
      }
      
      // Handle timeout errors
      if (error.name === 'AbortError') {
        return { 
          success: false, 
          message: 'Request timeout. Please try again.' 
        };
      }
      
      return { 
        success: false, 
        message: error?.message || 'An unexpected error occurred. Please try again.' 
      };
    } finally {
      setIsLoading(false);
    }
  };

  // Enhanced logout with cleanup
  const logout = async () => {
    try {
      // Attempt to call logout API
      await apiService.logout();
    } catch (error) {
      console.error('Logout error:', error);
      // Continue with local cleanup even if API call fails
    } finally {
      // Clear local state
      setUser(null);
      apiService.clearToken();
      
      // Redirect to login
      router.push('/login');
    }
  };

  // Update user data
  const updateUser = (userData: Partial<User>) => {
    if (user) {
      setUser({ ...user, ...userData });
    }
  };

  // Clear any authentication errors
  const clearErrors = () => {
    // This can be used to clear any error states
  };

  // Refresh user data
  const refreshUser = async () => {
    if (apiService.isAuthenticated()) {
      try {
        const response = await apiService.getProfile();
        if (response.success && response.data) {
          setUser(response.data.user);
        }
      } catch (error) {
        console.error('Failed to refresh user data:', error);
      }
    }
  };

  const value: AuthContextType = {
    user,
    isLoading,
    isAuthenticated: !!user,
    login,
    logout,
    updateUser,
    clearErrors,
    refreshUser,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

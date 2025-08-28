"use client";

import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { apiService, User, ApiResponse } from '../services/api';
import { useRouter } from 'next/navigation';

interface LoginResult {
  success: boolean;
  message?: string;
  errors?: Record<string, string[]>;
}

interface AuthContextType {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (agentCode: string, password: string) => Promise<LoginResult>;
  logout: () => Promise<void>;
  updateUser: (userData: Partial<User>) => void;
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
  const router = useRouter();

  useEffect(() => {
    // Check if user is already authenticated on app load
    const checkAuth = async () => {
      try {
        if (apiService.isAuthenticated()) {
          const response = await apiService.getProfile();
          if (response.success && response.data) {
            setUser(response.data.user);
          } else {
            // Token is invalid, clear it
            apiService.clearToken();
          }
        }
      } catch (error) {
        console.error('Auth check failed:', error);
        apiService.clearToken();
      } finally {
        setIsLoading(false);
      }
    };

    checkAuth();
  }, []);

  const login = async (agentCode: string, password: string): Promise<LoginResult> => {
    try {
      setIsLoading(true);
      const response = await apiService.login(agentCode, password);
      if (response.success && response.data) {
        setUser(response.data.user);
        return { success: true, message: 'Login successful' };
      }
      return { success: false, message: response.message || 'Login failed', errors: response.errors };
    } catch (error: any) {
      console.error('Login failed:', error);
      return { success: false, message: error?.message || 'Unable to login. Please try again.' };
    } finally {
      setIsLoading(false);
    }
  };

  const logout = async () => {
    try {
      await apiService.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      setUser(null);
      apiService.clearToken();
      router.push('/login');
    }
  };

  const updateUser = (userData: Partial<User>) => {
    if (user) {
      setUser({ ...user, ...userData });
    }
  };

  const value: AuthContextType = {
    user,
    isLoading,
    isAuthenticated: !!user,
    login,
    logout,
    updateUser,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

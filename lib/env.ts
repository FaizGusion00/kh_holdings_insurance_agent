/**
 * Centralized Environment Configuration
 * 
 * This file provides a centralized way to manage environment variables
 * and easily switch between development and production configurations.
 * 
 * Usage:
 * - Import: import { env } from '@/lib/env'
 * - Use: env.API_URL, env.APP_NAME, etc.
 */

// Environment types
export type Environment = 'development' | 'production' | 'test';

// Environment configuration interface
export interface EnvConfig {
  // API Configuration
  API_URL: string;
  API_TIMEOUT: number;
  
  // App Configuration
  APP_NAME: string;
  APP_VERSION: string;
  NODE_ENV: Environment;
  
  // Feature Flags
  ENABLE_ANALYTICS: boolean;
  ENABLE_DEBUG: boolean;
  ENABLE_DEV_TOOLS: boolean;
  
  // Environment Info
  IS_DEVELOPMENT: boolean;
  IS_PRODUCTION: boolean;
  IS_TEST: boolean;
}

// Get current environment
const getCurrentEnvironment = (): Environment => {
  const nodeEnv = process.env.NODE_ENV as Environment;
  return nodeEnv || 'development';
};

// Environment-specific configurations
const environmentConfigs: Record<Environment, Partial<EnvConfig>> = {
  development: {
    API_URL: 'http://localhost:8000/api',
    API_TIMEOUT: 30000,
    APP_NAME: 'KHH Insurance Agent Portal (Dev)',
    APP_VERSION: '1.0.0-dev',
    ENABLE_ANALYTICS: false,
    ENABLE_DEBUG: true,
    ENABLE_DEV_TOOLS: true,
  },
  production: {
    API_URL: 'https://api.khholdings.example.com/api',
    API_TIMEOUT: 15000,
    APP_NAME: 'KHH Insurance Agent Portal',
    APP_VERSION: '1.0.0',
    ENABLE_ANALYTICS: true,
    ENABLE_DEBUG: false,
    ENABLE_DEV_TOOLS: false,
  },
  test: {
    API_URL: 'http://localhost:8000/api',
    API_TIMEOUT: 5000,
    APP_NAME: 'KHH Insurance Agent Portal (Test)',
    APP_VERSION: '1.0.0-test',
    ENABLE_ANALYTICS: false,
    ENABLE_DEBUG: true,
    ENABLE_DEV_TOOLS: false,
  },
};

// Create environment configuration
const createEnvConfig = (): EnvConfig => {
  const currentEnv = getCurrentEnvironment();
  const baseConfig = environmentConfigs[currentEnv];
  
  // Override with environment variables if they exist
  const config: EnvConfig = {
    // API Configuration
    API_URL: process.env.NEXT_PUBLIC_API_URL || baseConfig.API_URL || 'http://localhost:8000/api',
    API_TIMEOUT: parseInt(process.env.NEXT_PUBLIC_API_TIMEOUT || '30000', 10),
    
    // App Configuration
    APP_NAME: process.env.NEXT_PUBLIC_APP_NAME || baseConfig.APP_NAME || 'KHH Insurance Agent Portal',
    APP_VERSION: process.env.NEXT_PUBLIC_APP_VERSION || baseConfig.APP_VERSION || '1.0.0',
    NODE_ENV: currentEnv,
    
    // Feature Flags
    ENABLE_ANALYTICS: process.env.NEXT_PUBLIC_ENABLE_ANALYTICS === 'true' || baseConfig.ENABLE_ANALYTICS || false,
    ENABLE_DEBUG: process.env.NEXT_PUBLIC_ENABLE_DEBUG === 'true' || baseConfig.ENABLE_DEBUG || false,
    ENABLE_DEV_TOOLS: process.env.NEXT_PUBLIC_ENABLE_DEV_TOOLS === 'true' || baseConfig.ENABLE_DEV_TOOLS || false,
    
    // Environment Info
    IS_DEVELOPMENT: currentEnv === 'development',
    IS_PRODUCTION: currentEnv === 'production',
    IS_TEST: currentEnv === 'test',
  };
  
  return config;
};

// Export the environment configuration
export const env = createEnvConfig();

// Export utility functions
export const isDevelopment = () => env.IS_DEVELOPMENT;
export const isProduction = () => env.IS_PRODUCTION;
export const isTest = () => env.IS_TEST;

// Export environment switching helper
export const getEnvironmentInfo = () => ({
  current: env.NODE_ENV,
  apiUrl: env.API_URL,
  appName: env.APP_NAME,
  version: env.APP_VERSION,
  debug: env.ENABLE_DEBUG,
  analytics: env.ENABLE_ANALYTICS,
});

// Console log environment info in development
if (env.IS_DEVELOPMENT && env.ENABLE_DEBUG) {
  console.log('🔧 Environment Configuration:', getEnvironmentInfo());
}

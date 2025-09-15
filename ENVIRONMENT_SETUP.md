# Environment Configuration Guide

This project uses a centralized environment configuration system that makes it easy to switch between development and production environments.

## 🚀 Quick Start

### Switch to Development Environment
```bash
npm run env:dev
npm run dev
```

### Switch to Production Environment
```bash
npm run env:prod
npm run dev
```

### Check Current Environment
```bash
npm run env:status
```

## 📁 Environment Files

The project uses the following environment files:

- `.env.development` - Development environment configuration
- `.env.production` - Production environment configuration
- `.env.local` - Active environment configuration (auto-generated)

## 🔧 Configuration Structure

### Development Environment (`.env.development`)
```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_APP_NAME=KHH Insurance Agent Portal (Dev)
NEXT_PUBLIC_ENABLE_ANALYTICS=false
NEXT_PUBLIC_ENABLE_DEBUG=true
```

### Production Environment (`.env.production`)
```env
NEXT_PUBLIC_API_URL=https://api.khholdings.example.com/api
NEXT_PUBLIC_APP_NAME=KHH Insurance Agent Portal
NEXT_PUBLIC_ENABLE_ANALYTICS=true
NEXT_PUBLIC_ENABLE_DEBUG=false
```

## 🛠️ Available Scripts

### Environment Switching
- `npm run env:dev` - Switch to development environment
- `npm run env:prod` - Switch to production environment
- `npm run env:test` - Switch to test environment
- `npm run env:status` - Check current environment status

### Development
- `npm run dev` - Start development server (uses current environment)
- `npm run dev:prod` - Start development server with production environment

### Building
- `npm run build` - Build for current environment
- `npm run build:dev` - Build for development environment
- `npm run build:prod` - Build for production environment

## 💻 Usage in Code

### Import Environment Configuration
```typescript
import { env } from '@/lib/env';

// Use environment variables
const apiUrl = env.API_URL;
const appName = env.APP_NAME;
const isDebug = env.ENABLE_DEBUG;
```

### Available Environment Properties
```typescript
interface EnvConfig {
  // API Configuration
  API_URL: string;
  API_TIMEOUT: number;
  
  // App Configuration
  APP_NAME: string;
  APP_VERSION: string;
  NODE_ENV: 'development' | 'production' | 'test';
  
  // Feature Flags
  ENABLE_ANALYTICS: boolean;
  ENABLE_DEBUG: boolean;
  ENABLE_DEV_TOOLS: boolean;
  
  // Environment Info
  IS_DEVELOPMENT: boolean;
  IS_PRODUCTION: boolean;
  IS_TEST: boolean;
}
```

### Utility Functions
```typescript
import { isDevelopment, isProduction, getEnvironmentInfo } from '@/lib/env';

// Check environment
if (isDevelopment()) {
  console.log('Running in development mode');
}

// Get environment info
const envInfo = getEnvironmentInfo();
console.log('Current environment:', envInfo.current);
```

## 🔄 How It Works

1. **Environment Detection**: The system automatically detects the current environment based on `NODE_ENV`
2. **Configuration Loading**: Loads the appropriate base configuration for the environment
3. **Variable Override**: Environment variables can override base configuration
4. **Centralized Access**: All configuration is accessed through the `env` object

## 📝 Adding New Environment Variables

1. **Add to environment files**: Add the variable to both `.env.development` and `.env.production`
2. **Update TypeScript interface**: Add the variable to the `EnvConfig` interface in `lib/env.ts`
3. **Add to configuration**: Add the variable to the `createEnvConfig` function

Example:
```typescript
// In lib/env.ts
export interface EnvConfig {
  // ... existing properties
  NEW_FEATURE_ENABLED: boolean;
}

// In createEnvConfig function
const config: EnvConfig = {
  // ... existing properties
  NEW_FEATURE_ENABLED: process.env.NEXT_PUBLIC_NEW_FEATURE_ENABLED === 'true' || baseConfig.NEW_FEATURE_ENABLED || false,
};
```

## 🚨 Important Notes

- **Never commit `.env.local`**: This file is auto-generated and should be in `.gitignore`
- **Always use `NEXT_PUBLIC_` prefix**: For client-side environment variables
- **Restart after switching**: Always restart your development server after switching environments
- **Test both environments**: Make sure your app works in both development and production

## 🔍 Troubleshooting

### Environment Not Switching
1. Check if the environment file exists
2. Restart your development server
3. Check the console for environment configuration logs

### API URL Not Updating
1. Verify the environment file has the correct `NEXT_PUBLIC_API_URL`
2. Check if the API service is importing from the centralized config
3. Clear browser cache and restart

### Build Issues
1. Make sure all environment variables are properly defined
2. Check if the environment file is being loaded correctly
3. Verify the Next.js configuration

## 📞 Support

If you encounter any issues with the environment configuration, check:
1. The console logs for environment information
2. The `.env.local` file content
3. The environment switching script output

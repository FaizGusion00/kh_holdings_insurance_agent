#!/usr/bin/env node

/**
 * Environment Switching Script
 * 
 * This script allows easy switching between development and production environments
 * by copying the appropriate .env file to .env.local
 * 
 * Usage:
 * - node scripts/switch-env.js development
 * - node scripts/switch-env.js production
 * - node scripts/switch-env.js test
 */

const fs = require('fs');
const path = require('path');

const environments = ['development', 'production', 'test'];
const targetEnv = process.argv[2];

if (!targetEnv || !environments.includes(targetEnv)) {
  console.log('❌ Please specify a valid environment:');
  console.log('   node scripts/switch-env.js development');
  console.log('   node scripts/switch-env.js production');
  console.log('   node scripts/switch-env.js test');
  process.exit(1);
}

const sourceFile = `.env.${targetEnv}`;
const targetFile = '.env.local';

// Check if source file exists
if (!fs.existsSync(sourceFile)) {
  console.log(`❌ Environment file ${sourceFile} not found!`);
  process.exit(1);
}

try {
  // Copy environment file
  fs.copyFileSync(sourceFile, targetFile);
  
  console.log(`✅ Successfully switched to ${targetEnv} environment`);
  console.log(`   Copied ${sourceFile} to ${targetFile}`);
  
  // Display current configuration
  const envContent = fs.readFileSync(targetFile, 'utf8');
  const lines = envContent.split('\n').filter(line => line.trim() && !line.startsWith('#'));
  
  console.log('\n📋 Current Configuration:');
  lines.forEach(line => {
    const [key, value] = line.split('=');
    if (key && value) {
      console.log(`   ${key}=${value}`);
    }
  });
  
  console.log('\n🚀 Next steps:');
  console.log('   1. Restart your development server');
  console.log('   2. The app will now use the new environment configuration');
  
} catch (error) {
  console.log(`❌ Error switching environment: ${error.message}`);
  process.exit(1);
}

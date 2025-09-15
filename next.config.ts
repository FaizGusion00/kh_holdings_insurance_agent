import type { NextConfig } from "next";

const nextConfig: NextConfig = {
	// Environment configuration
	env: {
		CUSTOM_KEY: process.env.CUSTOM_KEY,
	},
	
	// Webpack configuration
	webpack: (config) => {
		// Avoid WasmHash crashes by disabling wasm experiments and using stable hash
		config.experiments = {
			...config.experiments,
			asyncWebAssembly: false,
			syncWebAssembly: false,
		};
		if (config.output) {
			config.output.hashFunction = "xxhash64" as any;
		}
		return config;
	},
	
	// Enable environment-specific builds
	experimental: {
		// Enable environment-specific optimizations
		optimizeCss: process.env.NODE_ENV === 'production',
	},
	
	// Environment-specific settings
	...(process.env.NODE_ENV === 'production' && {
		// Production-specific optimizations
		compress: true,
		poweredByHeader: false,
		generateEtags: false,
	}),
};

export default nextConfig;

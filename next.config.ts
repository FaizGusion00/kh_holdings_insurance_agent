import type { NextConfig } from "next";

const nextConfig: NextConfig = {
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
};

export default nextConfig;

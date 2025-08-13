"use client";

import { motion } from "framer-motion";

interface LoadingSpinnerProps {
	size?: "sm" | "md" | "lg";
	text?: string;
}

export function LoadingSpinner({ size = "md", text = "Loading..." }: LoadingSpinnerProps) {
	const sizeClasses = {
		sm: "w-8 h-8",
		md: "w-16 h-16",
		lg: "w-24 h-24"
	};

	const textSizes = {
		sm: "text-sm",
		md: "text-base",
		lg: "text-lg"
	};

	return (
		<div className="flex flex-col items-center justify-center space-y-6">
			{/* Main Spinner Container */}
			<div className="relative flex items-center justify-center">
				{/* Outer Ring */}
				<motion.div
					className={`${sizeClasses[size]} border-4 border-blue-100 rounded-full`}
					animate={{ rotate: 360 }}
					transition={{
						duration: 2,
						repeat: Infinity,
						ease: "linear"
					}}
				/>
				
				{/* Middle Ring */}
				<motion.div
					className={`${sizeClasses[size]} border-4 border-transparent border-t-blue-400 rounded-full absolute inset-0`}
					animate={{ rotate: -360 }}
					transition={{
						duration: 1.5,
						repeat: Infinity,
						ease: "linear"
					}}
				/>
				
				{/* Inner Ring */}
				<motion.div
					className={`${sizeClasses[size]} border-4 border-transparent border-t-blue-600 rounded-full absolute inset-0`}
					animate={{ rotate: 360 }}
					transition={{
						duration: 1,
						repeat: Infinity,
						ease: "linear"
					}}
				/>
				
				{/* Center Dot */}
				<motion.div
					className="absolute inset-0 flex items-center justify-center"
					animate={{ scale: [1, 1.2, 1] }}
					transition={{
						duration: 2,
						repeat: Infinity,
						ease: "easeInOut"
					}}
				>
					<div className="w-3 h-3 bg-blue-600 rounded-full shadow-lg" />
				</motion.div>
			</div>

			{/* Loading Text */}
			<motion.div
				className={`${textSizes[size]} text-blue-600 font-medium text-center`}
				animate={{ opacity: [0.6, 1, 0.6] }}
				transition={{
					duration: 2,
					repeat: Infinity,
					ease: "easeInOut"
				}}
			>
				{text}
			</motion.div>

			{/* Floating Dots */}
			<div className="flex items-center justify-center space-x-3">
				{Array.from({ length: 3 }).map((_, i) => (
					<motion.div
						key={i}
						className="w-2 h-2 bg-blue-400 rounded-full"
						animate={{
							y: [0, -8, 0],
							opacity: [0.4, 1, 0.4],
							scale: [0.8, 1.2, 0.8]
						}}
						transition={{
							duration: 1.5,
							repeat: Infinity,
							delay: i * 0.2,
							ease: "easeInOut"
						}}
					/>
				))}
			</div>
		</div>
	);
}

// Full Screen Loading Overlay
export function LoadingOverlay({ text = "Authenticating..." }: { text?: string }) {
	return (
		<motion.div
			className="fixed inset-0 bg-white/95 backdrop-blur-sm z-50 flex items-center justify-center"
			initial={{ opacity: 0 }}
			animate={{ opacity: 1 }}
			exit={{ opacity: 0 }}
			transition={{ duration: 0.3 }}
		>
			<div className="text-center max-w-sm mx-auto px-6">
				{/* Logo/Brand Section */}
				<motion.div
					initial={{ opacity: 0, y: -20 }}
					animate={{ opacity: 1, y: 0 }}
					transition={{ delay: 0.1, duration: 0.5 }}
					className="mb-8"
				>
					<div className="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center shadow-lg">
						<span className="text-white text-2xl font-bold">KH</span>
					</div>
					<h3 className="text-lg font-semibold text-gray-800">Koperasi Kumpulan KH Berhad</h3>
				</motion.div>

				{/* Loading Spinner */}
				<LoadingSpinner size="lg" text={text} />
				
				{/* Progress Bar */}
				<motion.div
					className="mt-8 w-full max-w-xs mx-auto"
					initial={{ opacity: 0, y: 20 }}
					animate={{ opacity: 1, y: 0 }}
					transition={{ delay: 0.3, duration: 0.5 }}
				>
					<div className="w-full h-2 bg-blue-100 rounded-full overflow-hidden shadow-inner">
						<motion.div
							className="h-full bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 rounded-full"
							initial={{ width: "0%" }}
							animate={{ width: "100%" }}
							transition={{
								duration: 2.5,
								ease: "easeInOut"
							}}
						/>
					</div>
					<div className="mt-2 text-xs text-gray-500 text-center">
						Verifying credentials...
					</div>
				</motion.div>

				{/* Status Messages */}
				<motion.div
					className="mt-6 space-y-2"
					initial={{ opacity: 0 }}
					animate={{ opacity: 1 }}
					transition={{ delay: 0.5, duration: 0.5 }}
				>
					<div className="flex items-center justify-center space-x-2 text-sm text-gray-600">
						<div className="w-2 h-2 bg-green-400 rounded-full animate-pulse" />
						<span>Connecting to secure server</span>
					</div>
					<div className="flex items-center justify-center space-x-2 text-sm text-gray-600">
						<div className="w-2 h-2 bg-blue-400 rounded-full animate-pulse" />
						<span>Validating user credentials</span>
					</div>
					<div className="flex items-center justify-center space-x-2 text-sm text-gray-600">
						<div className="w-2 h-2 bg-purple-400 rounded-full animate-pulse" />
						<span>Preparing your dashboard</span>
					</div>
				</motion.div>
			</div>
		</motion.div>
	);
}

"use client";

import { motion } from "framer-motion";

interface LoadingOverlayProps {
	text?: string;
}

export function LoadingOverlay({ text = "Loading..." }: LoadingOverlayProps) {
	return (
		<motion.div
			initial={{ opacity: 0 }}
			animate={{ opacity: 1 }}
			exit={{ opacity: 0 }}
			className="fixed inset-0 z-[70] bg-black/20 backdrop-blur-sm flex items-center justify-center"
		>
			<motion.div
				initial={{ scale: 0.8, opacity: 0 }}
				animate={{ scale: 1, opacity: 1 }}
				transition={{ type: "spring", stiffness: 200, damping: 20 }}
				className="bg-white rounded-2xl shadow-2xl border border-gray-100 p-8 max-w-sm mx-4"
			>
				<div className="flex flex-col items-center space-y-6">
					{/* Modern Minimalist Spinner */}
					<div className="relative">
						{/* Outer ring */}
						<motion.div
							animate={{ rotate: 360 }}
							transition={{ duration: 2, repeat: Infinity, ease: "linear" }}
							className="w-16 h-16 border-4 border-gray-200 rounded-full"
						/>
						{/* Inner ring with gradient */}
						<motion.div
							animate={{ rotate: -360 }}
							transition={{ duration: 1.5, repeat: Infinity, ease: "linear" }}
							className="absolute inset-0 w-16 h-16 border-4 border-transparent border-t-blue-500 border-r-emerald-500 rounded-full"
						/>
						{/* Center dot */}
						<motion.div
							animate={{ scale: [1, 1.2, 1] }}
							transition={{ duration: 1.5, repeat: Infinity, ease: "easeInOut" }}
							className="absolute inset-0 w-16 h-16 flex items-center justify-center"
						>
							<div className="w-2 h-2 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full" />
						</motion.div>
					</div>

					{/* Loading text */}
					<motion.div
						initial={{ opacity: 0, y: 10 }}
						animate={{ opacity: 1, y: 0 }}
						transition={{ delay: 0.2 }}
						className="text-center"
					>
						<motion.p
							animate={{ opacity: [0.6, 1, 0.6] }}
							transition={{ duration: 2, repeat: Infinity, ease: "easeInOut" }}
							className="text-gray-700 font-medium text-lg"
						>
							{text}
						</motion.p>
						<motion.div
							animate={{ opacity: [0.3, 0.7, 0.3] }}
							transition={{ duration: 2, repeat: Infinity, ease: "easeInOut" }}
							className="text-gray-500 text-sm mt-1"
						>
							Please wait...
						</motion.div>
					</motion.div>

					{/* Progress dots */}
					<div className="flex space-x-2">
						{Array.from({ length: 3 }).map((_, i) => (
							<motion.div
								key={i}
								animate={{ 
									scale: [1, 1.3, 1],
									opacity: [0.4, 1, 0.4]
								}}
								transition={{ 
									duration: 1.5, 
									repeat: Infinity, 
									delay: i * 0.2,
									ease: "easeInOut"
								}}
								className="w-2 h-2 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full"
							/>
						))}
					</div>
				</div>
			</motion.div>
		</motion.div>
	);
}

// Simple inline spinner for buttons and small components
export function LoadingSpinner({ size = "sm", className = "" }: { size?: "xs" | "sm" | "md" | "lg"; className?: string }) {
	const sizeClasses = {
		xs: "w-3 h-3",
		sm: "w-4 h-4",
		md: "w-6 h-6",
		lg: "w-8 h-8"
	};

	return (
		<motion.div
			animate={{ rotate: 360 }}
			transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
			className={`${sizeClasses[size]} ${className}`}
		>
			<div className="w-full h-full border-2 border-gray-200 border-t-blue-500 rounded-full" />
		</motion.div>
	);
}

// Compact loading indicator for tables and lists
export function LoadingRow({ colSpan = 1 }: { colSpan?: number }) {
	return (
		<tr>
			<td colSpan={colSpan} className="py-8">
				<div className="flex items-center justify-center space-x-3">
					<LoadingSpinner size="sm" />
					<span className="text-gray-500 text-sm">Loading...</span>
				</div>
			</td>
		</tr>
	);
}

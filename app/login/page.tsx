"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import { Button } from "../(ui)/components/ui";
import { LoadingOverlay } from "../(ui)/components/LoadingSpinner";
import { PageTransition, FadeIn, SlideUp, ScaleIn } from "../(ui)/components/PageTransition";
import { useRouter } from "next/navigation";

export default function LoginPage() {
	const router = useRouter();
	const [isLoading, setIsLoading] = useState(false);
	const [errors, setErrors] = useState<string[]>([]);
	const [formData, setFormData] = useState({
		phone: "",
		password: ""
	});

	const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		const value = e.target.value;
		
		// Only allow digits for agent number
		if (e.target.name === "phone") {
			const digitsOnly = value.replace(/\D/g, '');
			if (digitsOnly.length <= 6) {
				setFormData(prev => ({
					...prev,
					[e.target.name]: digitsOnly
				}));
			}
		} else {
			setFormData(prev => ({
				...prev,
				[e.target.name]: value
			}));
		}
		
		// Clear errors when user starts typing
		if (errors.length > 0) {
			setErrors([]);
		}
	};

	const validateForm = () => {
		const newErrors: string[] = [];
		
		if (!formData.phone.trim()) {
			newErrors.push("Agent number is required");
		} else if (formData.phone.trim().length < 5) {
			newErrors.push("Agent number must be at least 5 digits");
		} else if (formData.phone.trim().length > 6) {
			newErrors.push("Agent number must not exceed 6 digits");
		}
		
		if (!formData.password.trim()) {
			newErrors.push("Password is required");
		} else if (formData.password.trim().length < 6) {
			newErrors.push("Password must be at least 6 characters");
		}
		
		setErrors(newErrors);
		return newErrors.length === 0;
	};

	const handleLogin = async (e: React.FormEvent) => {
		e.preventDefault();
		
		if (!validateForm()) {
			return;
		}

		setIsLoading(true);

		try {
			// Simulate API call with realistic timing
			await new Promise(resolve => setTimeout(resolve, 2500));
			
			// Success - redirect to dashboard
			console.log("Login successful, redirecting to dashboard...");
			router.push("/dashboard");
			
		} catch (error) {
			console.error("Login error:", error);
			setErrors(["Login failed. Please try again."]);
		} finally {
			setIsLoading(false);
		}
	};

	return (
		<>
			{isLoading && <LoadingOverlay text="Authenticating..." />}

			{/* Animated Background */}
			<motion.div 
				className="fixed inset-0 -z-10 overflow-hidden"
				initial={{ opacity: 0 }}
				animate={{ opacity: 1 }}
				transition={{ duration: 2, ease: "easeOut" }}
			>
				{/* Main gradient background */}
				<motion.div 
					className="absolute inset-0 bg-gradient-to-br from-white via-blue-50 to-indigo-100"
					animate={{
						background: [
							"linear-gradient(135deg, #ffffff 0%, #ffffff 50%, #eff6ff 70%, #e0e7ff 100%)",
							"linear-gradient(135deg, #ffffff 0%, #ffffff 50%, #dbeafe 70%, #c7d2fe 100%)",
							"linear-gradient(135deg, #ffffff 0%, #ffffff 50%, #eff6ff 70%, #e0e7ff 100%)"
						]
					}}
					transition={{
						duration: 12,
						repeat: Infinity,
						ease: "easeInOut",
						times: [0, 0.5, 1]
					}}
				/>
				
				{/* Animated floating shapes */}
				<motion.div
					className="absolute top-1/4 left-1/4 w-32 h-32 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-full opacity-40 blur-xl"
					animate={{
						x: [0, 40, -20, 0],
						y: [0, -30, 20, 0],
						scale: [1, 1.15, 0.9, 1],
						rotate: [0, 180, 360, 0]
					}}
					transition={{
						duration: 20,
						repeat: Infinity,
						ease: "easeInOut",
						times: [0, 0.33, 0.66, 1]
					}}
				/>
				
				<motion.div
					className="absolute top-1/3 right-1/4 w-24 h-24 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full opacity-40 blur-xl"
					animate={{
						x: [0, -35, 25, 0],
						y: [0, 25, -15, 0],
						scale: [1, 0.85, 1.1, 1],
						rotate: [0, -180, -360, 0]
					}}
					transition={{
						duration: 25,
						repeat: Infinity,
						ease: "easeInOut",
						times: [0, 0.33, 0.66, 1]
					}}
				/>
				
				<motion.div
					className="absolute bottom-1/4 left-1/3 w-40 h-40 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full opacity-35 blur-2xl"
					animate={{
						x: [0, 30, -25, 0],
						y: [0, -40, 30, 0],
						scale: [1, 1.25, 0.8, 1],
						rotate: [0, 90, 270, 0]
					}}
					transition={{
						duration: 30,
						repeat: Infinity,
						ease: "easeInOut",
						times: [0, 0.33, 0.66, 1]
					}}
				/>
				
				{/* Additional subtle floating elements */}
				<motion.div
					className="absolute top-1/2 right-1/3 w-16 h-16 bg-gradient-to-r from-blue-300 to-indigo-400 rounded-full opacity-25 blur-lg"
					animate={{
						x: [0, 15, -10, 0],
						y: [0, -20, 15, 0],
						scale: [1, 1.1, 0.9, 1]
					}}
					transition={{
						duration: 18,
						repeat: Infinity,
						ease: "easeInOut",
						times: [0, 0.33, 0.66, 1]
					}}
				/>
				
				<motion.div
					className="absolute bottom-1/3 right-1/2 w-20 h-20 bg-gradient-to-r from-indigo-300 to-purple-400 rounded-full opacity-20 blur-lg"
					animate={{
						x: [0, -20, 25, 0],
						y: [0, 30, -25, 0],
						scale: [1, 0.9, 1.15, 1]
					}}
					transition={{
						duration: 22,
						repeat: Infinity,
						ease: "easeInOut",
						times: [0, 0.33, 0.66, 1]
					}}
				/>
				
				{/* Bottom wave effect with smoother animation */}
				<motion.div
					className="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-r from-blue-400 via-indigo-500 to-purple-500"
					style={{
						clipPath: "polygon(0 100%, 100% 100%, 100% 0, 80% 50%, 60% 0, 40% 50%, 20% 0, 0 50%)"
					}}
					animate={{
						clipPath: [
							"polygon(0 100%, 100% 100%, 100% 0, 80% 50%, 60% 0, 40% 50%, 20% 0, 0 50%)",
							"polygon(0 100%, 100% 100%, 100% 0, 85% 35%, 70% 0, 55% 65%, 35% 0, 15% 45%, 0 35%)",
							"polygon(0 100%, 100% 100%, 100% 0, 90% 40%, 75% 0, 60% 60%, 45% 0, 30% 50%, 15% 0, 0 40%)",
							"polygon(0 100%, 100% 100%, 100% 0, 80% 50%, 60% 0, 40% 50%, 20% 0, 0 50%)"
						]
					}}
					transition={{
						duration: 15,
						repeat: Infinity,
						ease: "easeInOut",
						times: [0, 0.25, 0.5, 1]
					}}
				/>
			</motion.div>

			<PageTransition>
				<div className="min-h-screen flex items-center justify-center p-3 sm:p-4 lg:p-6">
					<div className="w-full max-w-4xl xl:max-w-6xl rounded-3xl shadow-2xl overflow-hidden relative">
						{/* Green gradient border with proper structure */}
						<div className="absolute inset-0 rounded-3xl bg-gradient-to-r from-emerald-400 via-green-500 to-teal-500 p-[3px]">
							<div className="w-full h-full bg-white rounded-3xl"></div>
						</div>
						
						<div className="relative z-10 grid grid-cols-1 md:grid-cols-2">
							{/* Left Section - Logo */}
							<FadeIn delay={0.2}>
								<motion.div
									initial={{ opacity: 0, x: -20 }}
									animate={{ opacity: 1, x: 0 }}
									transition={{ duration: 0.8, ease: [0.25, 0.46, 0.45, 0.94] }}
									className="flex items-center justify-center p-8 lg:p-12"
								>
									<Image
										src="/logo.png"
										alt="WeKongsi"
										width={520}
										height={280}
										priority
										className="w-[200px] sm:w-[280px] md:w-[320px] lg:w-[400px] h-auto"
									/>
								</motion.div>
							</FadeIn>
							
							{/* Right Section - Login Form */}
							<SlideUp delay={0.4}>
								<motion.div
									initial={{ opacity: 0, x: 20 }}
									animate={{ opacity: 1, x: 0 }}
									transition={{ duration: 0.8, ease: [0.25, 0.46, 0.45, 0.94] }}
									className="flex items-center justify-center p-8 lg:p-12"
								>
									<div className="w-full max-w-md">
										<ScaleIn delay={0.6}>
											<h2 className="text-2xl sm:text-3xl font-semibold text-center mb-2">Login Now</h2>
											<p className="text-gray-600 text-center mb-8">Please enter the details below to continue</p>
										</ScaleIn>

										{/* Error Messages */}
										{errors.length > 0 && (
											<motion.div
												initial={{ opacity: 0, y: -10, height: 0 }}
												animate={{ opacity: 1, y: 0, height: "auto" }}
												className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg"
											>
												{errors.map((error, index) => (
													<div key={index} className="text-red-600 text-sm flex items-center gap-2">
														<span className="w-2 h-2 bg-red-400 rounded-full" />
														{error}
													</div>
												))}
											</motion.div>
										)}

										<form className="space-y-6" onSubmit={handleLogin}>
											<FadeIn delay={0.8}>
												<div className="space-y-2">
													<label className="text-sm text-gray-700 font-medium">Agent Number</label>
													<div className="flex gap-2">
														<input 
															value="AGT" 
															disabled 
															className="w-16 h-12 rounded-lg border border-gray-200 bg-gray-50 text-center text-gray-600 font-medium" 
														/>
														<input 
															name="phone"
															value={formData.phone}
															onChange={handleInputChange}
															placeholder="12345" 
															maxLength={6}
															className="flex-1 h-12 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300 transition-all duration-200 input-enhanced" 
														/>
													</div>
													<p className="text-xs text-gray-500">Please enter your agent number</p>
												</div>
											</FadeIn>

											<FadeIn delay={1.0}>
												<div className="space-y-2">
													<label className="text-sm text-gray-700 font-medium">Password</label>
													<input 
														name="password"
														value={formData.password}
														onChange={handleInputChange}
														placeholder="Password" 
														type="password" 
														className="w-full h-12 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300 transition-all duration-200 input-enhanced" 
													/>
												</div>
											</FadeIn>

											<FadeIn delay={1.2}>
												<div className="flex items-center justify-end text-sm">
													<Link href="#" className="text-blue-600 hover:underline hover:text-blue-700 transition-colors">
														Forgot Password ?
													</Link>
												</div>
											</FadeIn>

											<FadeIn delay={1.4}>
												<Button 
													type="submit" 
													className="w-full h-12 text-base font-medium"
													loading={isLoading}
													loadingText="Authenticating..."
												>
													Login
												</Button>
											</FadeIn>
										</form>
									</div>
								</motion.div>
							</SlideUp>
						</div>
					</div>
				</div>
			</PageTransition>
		</>
	);
}



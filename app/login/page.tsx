"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
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
		setFormData(prev => ({
			...prev,
			[e.target.name]: e.target.value
		}));
		// Clear errors when user starts typing
		if (errors.length > 0) {
			setErrors([]);
		}
	};

	const validateForm = () => {
		const newErrors: string[] = [];
		
		if (!formData.phone.trim()) {
			newErrors.push("Phone number is required");
		} else if (formData.phone.trim().length < 8) {
			newErrors.push("Phone number must be at least 8 digits");
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
			<AnimatePresence>
				{isLoading && <LoadingOverlay text="Authenticating..." />}
			</AnimatePresence>

			<PageTransition>
				<div className="min-h-screen flex items-center justify-center p-4">
					<div className="w-full max-w-6xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
						<div className="grid grid-cols-1 md:grid-cols-2">
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
										<AnimatePresence>
											{errors.length > 0 && (
												<motion.div
													initial={{ opacity: 0, y: -10, height: 0 }}
													animate={{ opacity: 1, y: 0, height: "auto" }}
													exit={{ opacity: 0, y: -10, height: 0 }}
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
										</AnimatePresence>

										<form className="space-y-6" onSubmit={handleLogin}>
											<FadeIn delay={0.8}>
												<div className="space-y-2">
													<label className="text-sm text-gray-700 font-medium">Phone Number</label>
													<div className="flex gap-2">
														<input 
															value="+60" 
															disabled 
															className="w-16 h-12 rounded-lg border border-gray-200 bg-gray-50 text-center text-gray-600" 
														/>
														<input 
															name="phone"
															value={formData.phone}
															onChange={handleInputChange}
															placeholder="Phone Number" 
															className="flex-1 h-12 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300 transition-all duration-200 input-enhanced" 
														/>
													</div>
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



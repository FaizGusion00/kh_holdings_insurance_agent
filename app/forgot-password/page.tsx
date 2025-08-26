"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import { Button } from "../(ui)/components/ui";
import { LoadingOverlay } from "../(ui)/components/LoadingSpinner";
import { PageTransition, FadeIn, SlideUp, ScaleIn } from "../(ui)/components/PageTransition";
import { useRouter } from "next/navigation";
import { Shield, ArrowLeft, Mail, CheckCircle } from "lucide-react";

export default function ForgotPasswordPage() {
	const router = useRouter();
	const [isLoading, setIsLoading] = useState(false);
	const [isSuccess, setIsSuccess] = useState(false);
	const [errors, setErrors] = useState<string[]>([]);
	const [formData, setFormData] = useState({
		agentNumber: "",
		email: ""
	});

	const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		const { name, value } = e.target;
		
		// For agent number field, only allow numeric input
		if (name === 'agentNumber') {
			const numericValue = value.replace(/[^0-9]/g, '');
			setFormData(prev => ({
				...prev,
				[name]: numericValue
			}));
		} else {
			setFormData(prev => ({
				...prev,
				[name]: value
			}));
		}
		
		// Clear errors when user starts typing
		if (errors.length > 0) {
			setErrors([]);
		}
	};

	const validateForm = () => {
		const newErrors: string[] = [];
		
		if (!formData.agentNumber.trim()) {
			newErrors.push("Agent number is required");
		} else if (formData.agentNumber.trim().length < 5) {
			newErrors.push("Agent number must be at least 5 digits");
		} else if (formData.agentNumber.trim().length > 6) {
			newErrors.push("Agent number must be at most 6 digits");
		}
		
		if (!formData.email.trim()) {
			newErrors.push("Email address is required");
		} else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
			newErrors.push("Please enter a valid email address");
		}
		
		setErrors(newErrors);
		return newErrors.length === 0;
	};

	const handleSubmit = async (e: React.FormEvent) => {
		e.preventDefault();
		
		if (!validateForm()) {
			return;
		}

		setIsLoading(true);

		try {
			// Simulate API call with realistic timing
			await new Promise(resolve => setTimeout(resolve, 2000));
			
			// Success
			setIsSuccess(true);
			
		} catch (error) {
			console.error("Reset error:", error);
			setErrors(["Password reset failed. Please try again."]);
		} finally {
			setIsLoading(false);
		}
	};

	if (isSuccess) {
		return (
			<PageTransition>
				<div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6 bg-gradient-to-br from-blue-50 via-white to-emerald-50">
					<div className="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden relative">
						<div className="p-8 text-center">
							<motion.div
								initial={{ scale: 0 }}
								animate={{ scale: 1 }}
								transition={{ type: "spring", stiffness: 200, damping: 20 }}
								className="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-emerald-500 to-green-500 rounded-full flex items-center justify-center shadow-lg"
							>
								<CheckCircle className="w-10 h-10 text-white" />
							</motion.div>
							
							<motion.h2
								initial={{ opacity: 0, y: 20 }}
								animate={{ opacity: 1, y: 0 }}
								transition={{ delay: 0.2 }}
								className="text-2xl font-bold text-gray-800 mb-3"
							>
								Reset Link Sent!
							</motion.h2>
							
							<motion.p
								initial={{ opacity: 0, y: 20 }}
								animate={{ opacity: 1, y: 0 }}
								transition={{ delay: 0.4 }}
								className="text-gray-600 mb-6"
							>
								We&apos;ve sent a password reset link to your email address. Please check your inbox and follow the instructions to reset your password.
							</motion.p>
							
							<motion.div
								initial={{ opacity: 0, y: 20 }}
								animate={{ opacity: 1, y: 0 }}
								transition={{ delay: 0.6 }}
								className="space-y-3"
							>
								<Link href="/login">
									<Button className="w-full h-12 text-base font-semibold bg-gradient-to-r from-blue-600 to-emerald-600 hover:from-blue-700 hover:to-emerald-700 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
										Back to Login
									</Button>
								</Link>
							</motion.div>
						</div>
					</div>
				</div>
			</PageTransition>
		);
	}

	return (
		<>
			{isLoading && <LoadingOverlay text="Sending reset link..." />}

			<PageTransition>
				<div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6 bg-gradient-to-br from-blue-50 via-white to-emerald-50">
					<div className="w-full max-w-4xl xl:max-w-5xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden relative">
						{/* Background decorative elements */}
						<div className="absolute inset-0 bg-gradient-to-br from-blue-50/30 to-emerald-50/30 pointer-events-none" />
						<div className="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-100/20 to-transparent rounded-full -translate-y-16 translate-x-16" />
						<div className="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-emerald-100/20 to-transparent rounded-full translate-y-12 -translate-x-12" />
						
						<div className="grid grid-cols-1 md:grid-cols-2 relative">
							{/* Left Section - Enhanced Logo & Branding */}
							<FadeIn delay={0.2}>
								<motion.div
									initial={{ opacity: 0, x: -20 }}
									animate={{ opacity: 1, x: 0 }}
									transition={{ duration: 0.8, ease: [0.25, 0.46, 0.45, 0.94] }}
									className="flex flex-col items-center justify-center p-6 sm:p-8 md:p-10 lg:p-12 h-full bg-gradient-to-br from-blue-50/50 to-emerald-50/50 relative overflow-hidden"
								>
									<div className="text-center space-y-4">
										<Image
											src="/logo.png"
											alt="WeKongsi"
											width={520}
											height={280}
											priority
											className="w-[140px] sm:w-[180px] md:w-[240px] lg:w-[320px] h-auto drop-shadow-lg"
										/>
										<motion.div
											initial={{ opacity: 0, y: 20 }}
											animate={{ opacity: 1, y: 0 }}
											transition={{ delay: 0.8, duration: 0.6 }}
											className="space-y-2"
										>
											<h3 className="text-lg sm:text-xl md:text-2xl font-semibold text-gray-800">Forgot Password?</h3>
											<p className="text-gray-600 text-sm sm:text-base max-w-xs">Don&apos;t worry! We&apos;ll help you reset your password securely</p>
										</motion.div>
									</div>
								</motion.div>
							</FadeIn>
							
							{/* Right Section - Enhanced Form */}
							<SlideUp delay={0.4}>
								<motion.div
									initial={{ opacity: 0, x: 20 }}
									animate={{ opacity: 1, x: 0 }}
									transition={{ duration: 0.8, ease: [0.25, 0.46, 0.45, 0.94] }}
									className="flex items-center justify-center p-6 sm:p-8 md:p-10 lg:p-12 bg-white"
								>
									<div className="w-full max-w-sm sm:max-w-md">
										<ScaleIn delay={0.6}>
											<div className="text-center mb-8">
												<div className="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg">
													<Mail className="w-8 h-8 text-white" />
												</div>
												<h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800 mb-2">Reset Password</h2>
												<p className="text-gray-600 text-sm sm:text-base">Enter your details to receive a reset link</p>
											</div>
										</ScaleIn>

										{/* Error Messages */}
										{errors.length > 0 && (
											<motion.div
												initial={{ opacity: 0, y: -10, height: 0 }}
												animate={{ opacity: 1, y: 0, height: "auto" }}
												className="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl shadow-sm"
											>
												{errors.map((error, index) => (
													<div key={index} className="text-red-600 text-sm flex items-center gap-2">
														<span className="w-2 h-2 bg-red-400 rounded-full flex-shrink-0" />
														{error}
													</div>
												))}
											</motion.div>
										)}

										<form className="space-y-6" onSubmit={handleSubmit}>
											<FadeIn delay={0.8}>
												<div className="space-y-3">
													<label className="text-sm font-semibold text-gray-700 flex items-center gap-2">
														<span className="w-2 h-2 bg-blue-500 rounded-full" />
														Agent Number
													</label>
													<div className="flex gap-3">
														<input 
															value="AGT" 
															disabled 
															className="w-16 h-12 rounded-xl border-2 border-gray-200 bg-gray-50 text-center text-gray-600 font-semibold text-sm shadow-sm" 
														/>
														<input 
															name="agentNumber"
															value={formData.agentNumber}
															onChange={handleInputChange}
															placeholder="12345" 
															maxLength={6}
															pattern="[0-9]*"
															inputMode="numeric"
															className="flex-1 h-12 rounded-xl border-2 border-gray-200 px-4 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-300 text-sm font-medium shadow-sm hover:border-gray-300" 
														/>
													</div>
												</div>
											</FadeIn>

											<FadeIn delay={1.0}>
												<div className="space-y-3">
													<label className="text-sm font-semibold text-gray-700 flex items-center gap-2">
														<span className="w-2 h-2 bg-emerald-500 rounded-full" />
														Email Address
													</label>
													<input 
														name="email"
														type="email"
														value={formData.email}
														onChange={handleInputChange}
														placeholder="Enter your email address" 
														className="w-full h-12 rounded-xl border-2 border-gray-200 px-4 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 transition-all duration-300 text-sm font-medium shadow-sm hover:border-gray-300" 
													/>
												</div>
											</FadeIn>

											<FadeIn delay={1.2}>
												<Button 
													type="submit" 
													className="w-full h-12 text-base font-semibold bg-gradient-to-r from-blue-600 to-emerald-600 hover:from-blue-700 hover:to-emerald-700 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5"
													loading={isLoading}
													loadingText="Sending..."
												>
													Send Reset Link
												</Button>
											</FadeIn>
										</form>

										{/* Back to Login */}
										<motion.div
											initial={{ opacity: 0 }}
											animate={{ opacity: 1 }}
											transition={{ delay: 1.4, duration: 0.6 }}
											className="mt-6 text-center"
										>
											<Link 
												href="/login" 
												className="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800 transition-colors font-medium"
											>
												<ArrowLeft size={16} />
												Back to Login
											</Link>
										</motion.div>

										{/* Footer */}
										<motion.div
											initial={{ opacity: 0 }}
											animate={{ opacity: 1 }}
											transition={{ delay: 1.6, duration: 0.6 }}
											className="mt-8 text-center"
										>
											<p className="text-xs text-gray-500">
												Secure password reset powered by WeKongsi Insurance Platform
											</p>
										</motion.div>
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

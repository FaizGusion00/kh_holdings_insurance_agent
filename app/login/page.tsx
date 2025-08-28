"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import { Button } from "../(ui)/components/ui";
import { LoadingOverlay } from "../(ui)/components/LoadingSpinner";
import { PageTransition, FadeIn, SlideUp, ScaleIn } from "../(ui)/components/PageTransition";
import { useRouter } from "next/navigation";
import { Eye, EyeOff, Shield, Users, CheckCircle2, Info } from "lucide-react";
import { useAuth } from "../contexts/AuthContext";

export default function LoginPage() {
	const router = useRouter();
	const { login } = useAuth();
	const [isLoading, setIsLoading] = useState(false);
	const [errors, setErrors] = useState<string[]>([]);
	const [serverFieldErrors, setServerFieldErrors] = useState<Record<string, string[]>>({});
	const [successMsg, setSuccessMsg] = useState<string>("");
	const [showPassword, setShowPassword] = useState(false);
	const [formData, setFormData] = useState({
		agent_code_suffix: "", // 5 digits
		password: ""
	});

	const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		const { name, value } = e.target;
		if (name === 'agent_code_suffix') {
			const numeric = value.replace(/[^0-9]/g, '').slice(0, 5);
			setFormData(prev => ({ ...prev, agent_code_suffix: numeric }));
		} else {
			setFormData(prev => ({ ...prev, [name]: value }));
		}
		if (errors.length > 0) setErrors([]);
		if (Object.keys(serverFieldErrors).length > 0) setServerFieldErrors({});
		if (successMsg) setSuccessMsg("");
	};

	const validateForm = () => {
		const newErrors: string[] = [];
		if (!formData.agent_code_suffix.trim()) {
			newErrors.push("Agent code is required");
		} else if (!/^\d{5}$/.test(formData.agent_code_suffix.trim())) {
			newErrors.push("Agent code must be AGT + 5 digits, e.g. AGT00001");
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
		if (!validateForm()) return;
		setIsLoading(true);
		setErrors([]);
		setServerFieldErrors({});
		setSuccessMsg("");
		try {
			const agent_code = `AGT${formData.agent_code_suffix.padStart(5, '0')}`;
			const result = await login(agent_code, formData.password);
			if (result.success) {
				setSuccessMsg("Login successful. Redirecting to dashboard...");
				setTimeout(() => router.push("/dashboard"), 500);
			} else {
				const msgs: string[] = [];
				if (result.message) msgs.push(result.message);
				if (result.errors) {
					setServerFieldErrors(result.errors);
					Object.entries(result.errors).forEach(([field, arr]) => {
						arr.forEach(m => msgs.push(`${field}: ${m}`));
					});
				}
				if (msgs.length === 0) msgs.push("Invalid agent code or password.");
				setErrors(msgs);
			}
		} catch (error) {
			setErrors(["Login failed due to a network or server error."]);
		} finally {
			setIsLoading(false);
		}
	};

	return (
		<>
			{isLoading && <LoadingOverlay text="Authenticating..." />}

			<PageTransition>
				<div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6 bg-gradient-to-br from-blue-50 via-white to-blue-50">
					<div className="w-full max-w-5xl xl:max-w-6xl bg-white rounded-2xl sm:rounded-3xl shadow-xl sm:shadow-2xl border border-gray-100 overflow-hidden relative">
						<div className="absolute inset-0 bg-gradient-to-br from-blue-50/30 to-blue-50/30 pointer-events-none" />
						<div className="absolute top-0 right-0 w-20 h-20 sm:w-32 sm:h-32 bg-gradient-to-br from-blue-100/20 to-transparent rounded-full -translate-y-8 sm:-translate-y-16 translate-x-8 sm:translate-x-16" />
						<div className="absolute bottom-0 left-0 w-16 h-16 sm:w-24 sm:h-24 bg-gradient-to-tr from-blue-100/20 to-transparent rounded-full translate-y-6 sm:translate-y-12 -translate-x-6 sm:-translate-x-12" />
						
						<div className="grid grid-cols-1 md:grid-cols-2 relative">
							{/* Left Section */}
							<FadeIn delay={0.2}>
								<motion.div
									initial={{ opacity: 0, x: -20 }}
									animate={{ opacity: 1, x: 0 }}
									transition={{ duration: 0.8, ease: [0.25, 0.46, 0.45, 0.94] }}
									className="flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 h-full bg-gradient-to-br from-blue-50/50 to-blue-50/50 relative overflow-hidden"
								>
									<motion.div animate={{ y: [-10, 10, -10] }} transition={{ duration: 6, repeat: Infinity, ease: "easeInOut" }} className="absolute top-4 sm:top-8 right-4 sm:right-8 text-blue-200/40 sm:text-blue-200/60">
										<Shield size={20} className="sm:w-6 sm:h-6" />
									</motion.div>
									<motion.div animate={{ y: [10, -10, 10] }} transition={{ duration: 8, repeat: Infinity, ease: "easeInOut" }} className="absolute bottom-4 sm:bottom-8 left-4 sm:left-8 text-blue-200/40 sm:text-blue-200/60">
										<Users size={20} className="sm:w-6 sm:h-6" />
									</motion.div>
									<div className="text-center space-y-3 sm:space-y-4">
										<div className="flex justify-center">
											<Image src="/logo.png" alt="WeKongsi" width={520} height={280} priority className="w-[120px] sm:w-[160px] md:w-[200px] lg:w-[280px] xl:w-[320px] h-auto drop-shadow-lg" />
										</div>
										<motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.8, duration: 0.6 }} className="space-y-2">
											<h3 className="text-base sm:text-lg md:text-xl lg:text-2xl font-semibold text-gray-800">Welcome Back!</h3>
											<p className="text-gray-600 text-xs sm:text-sm md:text-base max-w-xs">Access your insurance management dashboard with secure authentication</p>
										</motion.div>
									</div>
								</motion.div>
							</FadeIn>
							
							{/* Right Section - Login Form */}
							<SlideUp delay={0.4}>
								<motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} transition={{ duration: 0.8, ease: [0.25, 0.46, 0.45, 0.94] }} className="flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 bg-white">
									<div className="w-full max-w-sm sm:max-w-md">
										<ScaleIn delay={0.6}>
											<div className="text-center mb-6 sm:mb-8">
												<div className="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 mx-auto mb-3 sm:mb-4 bg-gradient-to-br from-[#264EE4] to-[#264EE4] rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg">
													<Shield className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-white" />
												</div>
												<h2 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800 mb-2">Login Now</h2>
												<p className="text-gray-600 text-xs sm:text-sm md:text-base">Please enter the details below to continue</p>
											</div>
										</ScaleIn>

										{/* Success Message */}
										{successMsg && (
											<motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} className="mb-5 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-2">
												<CheckCircle2 className="w-4 h-4" />
												{successMsg}
											</motion.div>
										)}

										{/* Error Messages */}
										{(errors.length > 0 || Object.keys(serverFieldErrors).length > 0) && (
											<motion.div initial={{ opacity: 0, y: -10, height: 0 }} animate={{ opacity: 1, y: 0, height: "auto" }} className="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl shadow-sm space-y-2">
												{errors.map((error, index) => (
													<div key={`e-${index}`} className="text-red-700 text-sm flex items-center gap-2">
														<span className="w-2 h-2 bg-red-400 rounded-full flex-shrink-0" />
														{error}
													</div>
												))}
												{Object.entries(serverFieldErrors).map(([field, msgs]) => (
													<div key={field} className="text-red-700 text-sm flex items-start gap-2">
														<Info className="w-4 h-4 mt-0.5" />
														<div>
															<div className="font-semibold capitalize">{field.replaceAll('_',' ')}</div>
															<ul className="list-disc ml-5">
																{msgs.map((m, i) => (<li key={`${field}-${i}`}>{m}</li>))}
															</ul>
														</div>
													</div>
												))}
											</motion.div>
										)}

										<form className="space-y-4 sm:space-y-6" onSubmit={handleLogin}>
											<FadeIn delay={0.8}>
												<div className="space-y-3">
													<label className="text-sm font-semibold text-gray-700 flex items-center gap-2">
														<span className="w-2 h-2 bg-[#264EE4] rounded-full" />
														Agent Code
													</label>
													<div className="flex gap-2 sm:gap-3">
														<input value="AGT" disabled className="w-16 h-10 sm:h-12 rounded-lg sm:rounded-xl border-2 border-gray-200 bg-gray-50 text-center text-gray-600 font-semibold text-xs sm:text-sm shadow-sm" />
														<input name="agent_code_suffix" value={formData.agent_code_suffix} onChange={handleInputChange} placeholder="00001" maxLength={5} pattern="[0-9]*" inputMode="numeric" className="flex-1 h-10 sm:h-12 rounded-lg sm:rounded-xl border-2 border-gray-200 px-3 sm:px-4 focus:outline-none focus:ring-2 focus:ring-[#264EE4]/20 focus:border-[#264EE4] transition-all duration-300 text-xs sm:text-sm font-medium shadow-sm hover:border-gray-300 tracking-widest" />
													</div>
												</div>
											</FadeIn>

											<FadeIn delay={1.0}>
												<div className="space-y-3">
													<label className="text-sm font-semibold text-gray-700 flex items-center gap-2">
														<span className="w-2 h-2 bg-[#264EE4] rounded-full" />
														Password
													</label>
													<div className="relative">
														<input name="password" value={formData.password} onChange={handleInputChange} placeholder="Enter your password" type={showPassword ? "text" : "password"} className="w-full h-10 sm:h-12 rounded-lg sm:rounded-xl border-2 border-gray-200 px-3 sm:px-4 pr-10 sm:pr-12 focus:outline-none focus:ring-2 focus:ring-[#264EE4]/20 focus:border-[#264EE4] transition-all duration-300 text-xs sm:text-sm font-medium shadow-sm hover:border-gray-300" />
														<button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
															{showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
														</button>
													</div>
												</div>
											</FadeIn>

											<FadeIn delay={1.2}>
												<div className="flex items-center justify-end">
													<Link href="/forgot-password" className="text-sm text-[#264EE4] hover:text-[#264EE4]/80 hover:underline transition-colors font-medium">
														Forgot Password?
													</Link>
												</div>
											</FadeIn>

											<FadeIn delay={1.4}>
												<Button type="submit" className="w-full h-10 sm:h-12 text-sm sm:text-base font-semibold bg-gradient-to-r from-[#264EE4] to-[#264EE4] hover:from-[#264EE4]/90 hover:to-[#264EE4]/90 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5" loading={isLoading} loadingText="Authenticating...">
													Login
												</Button>
											</FadeIn>
										</form>

										<motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 1.6, duration: 0.6 }} className="mt-6 sm:mt-8 text-center">
											<p className="text-xs text-gray-500">Secure login powered by WeKongsi Insurance Platform</p>
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



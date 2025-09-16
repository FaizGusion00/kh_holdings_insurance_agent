"use client";

import { useState, useEffect, useCallback } from "react";
import { motion, AnimatePresence } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import { Button } from "../(ui)/components/ui";
import { LoadingOverlay } from "../(ui)/components/LoadingSpinner";
import { PageTransition, FadeIn, SlideUp, ScaleIn } from "../(ui)/components/PageTransition";
import { useRouter } from "next/navigation";
import { Eye, EyeOff, Shield, Users, CheckCircle2, Info, AlertCircle, Lock, User, Zap } from "lucide-react";
import { useAuth } from "../contexts/AuthContext";

// Enhanced validation rules
const VALIDATION_RULES = {
	agent_code: {
		required: "Agent code is required",
		pattern: "Agent code must be 5 digits",
		minLength: 5,
		maxLength: 5
	},
	password: {
		required: "Password is required",
		minLength: 6,
		minLengthMessage: "Password must be at least 6 characters"
	}
};

// Enhanced error types
interface FieldError {
	field: string;
	message: string;
	type: 'error' | 'warning' | 'info';
}

export default function LoginPage() {
	const router = useRouter();
	const { login } = useAuth();
	
	// Enhanced state management
	const [isLoading, setIsLoading] = useState(false);
	const [isSubmitting, setIsSubmitting] = useState(false);
	const [fieldErrors, setFieldErrors] = useState<FieldError[]>([]);
	const [serverFieldErrors, setServerFieldErrors] = useState<Record<string, string[]>>({});
	const [successMsg, setSuccessMsg] = useState<string>("");
	const [showPassword, setShowPassword] = useState(false);
	const [isFormValid, setIsFormValid] = useState(false);
	const [attemptCount, setAttemptCount] = useState(0);
	const [lockoutTime, setLockoutTime] = useState<number | null>(null);
	
	const [formData, setFormData] = useState({
		agent_code_suffix: "",
		password: ""
	});
	
	const [loginMode, setLoginMode] = useState<'agent_code' | 'email'>('agent_code');

	// Enhanced input validation with real-time feedback
	const validateField = useCallback((name: string, value: string): FieldError | null => {
		if (name === 'agent_code_suffix') {
			if (!value.trim()) {
				return { field: name, message: loginMode === 'email' ? 'Email is required' : VALIDATION_RULES.agent_code.required, type: 'error' };
			}
			
			if (loginMode === 'email') {
				// Email validation
				if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
					return { field: name, message: 'Please enter a valid email address', type: 'error' };
				}
			} else {
				// Agent code validation
				if (!/^\d{5}$/.test(value)) {
					return { field: name, message: VALIDATION_RULES.agent_code.pattern, type: 'error' };
				}
			}
		}
		
		if (name === 'password') {
			if (!value.trim()) {
				return { field: name, message: VALIDATION_RULES.password.required, type: 'error' };
			}
			if (value.length < VALIDATION_RULES.password.minLength) {
				return { field: name, message: VALIDATION_RULES.password.minLengthMessage, type: 'error' };
			}
		}
		
		return null;
	}, [loginMode]);

	// Real-time form validation
	useEffect(() => {
		const agentCodeError = validateField('agent_code_suffix', formData.agent_code_suffix);
		const passwordError = validateField('password', formData.password);
		
		const hasErrors = agentCodeError || passwordError;
		const minInputLength = loginMode === 'email' ? 3 : 5;
		setIsFormValid(!hasErrors && formData.agent_code_suffix.length >= minInputLength && formData.password.length >= 6);
	}, [formData, validateField, loginMode]);

	// Enhanced input change handler with real-time validation
	const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		const { name, value } = e.target;
		
		let processedValue = value;
		if (name === 'agent_code_suffix') {
			processedValue = value.replace(/[^0-9]/g, '').slice(0, 5);
		}
		
		setFormData(prev => ({ ...prev, [name]: processedValue }));
		
		// Clear field-specific errors
		setFieldErrors(prev => prev.filter(error => error.field !== name));
		setServerFieldErrors(prev => {
			const newErrors = { ...prev };
			delete newErrors[name];
			return newErrors;
		});
		
		// Clear success message on any change
		if (successMsg) setSuccessMsg("");
	};

	// Enhanced form validation
	const validateForm = (): boolean => {
		const errors: FieldError[] = [];
		
		// Validate agent code
		const agentCodeError = validateField('agent_code_suffix', formData.agent_code_suffix);
		if (agentCodeError) errors.push(agentCodeError);
		
		// Validate password
		const passwordError = validateField('password', formData.password);
		if (passwordError) errors.push(passwordError);
		
		setFieldErrors(errors);
		return errors.length === 0;
	};

	// Enhanced login handler with rate limiting and better error handling
	const handleLogin = async (e: React.FormEvent) => {
		e.preventDefault();
		
		// Check lockout
		if (lockoutTime && Date.now() < lockoutTime) {
			const remainingTime = Math.ceil((lockoutTime - Date.now()) / 1000);
			setFieldErrors([{
				field: 'general',
				message: `Too many failed attempts. Please wait ${remainingTime} seconds before trying again.`,
				type: 'warning'
			}]);
			return;
		}
		
		if (!validateForm()) return;
		
		setIsSubmitting(true);
		setFieldErrors([]);
		setServerFieldErrors({});
		setSuccessMsg("");
		
		try {
			const loginIdentifier = loginMode === 'email' 
				? formData.agent_code_suffix 
				: `AGT${formData.agent_code_suffix.padStart(5, '0')}`;
			const result = await login(loginIdentifier, formData.password);
			
			if (result.success) {
				setSuccessMsg("Login successful! Redirecting to dashboard...");
				setAttemptCount(0);
				
				// Enhanced success animation and redirect
				setTimeout(() => {
					router.push("/dashboard");
				}, 1000);
			} else {
				// Handle failed login attempts
				setAttemptCount(prev => prev + 1);
				
				// Implement progressive lockout
				if (attemptCount >= 4) {
					const lockoutDuration = Math.min(Math.pow(2, attemptCount - 4) * 60, 900) * 1000; // Max 15 minutes
					setLockoutTime(Date.now() + lockoutDuration);
					
					setFieldErrors([{
						field: 'general',
						message: `Account temporarily locked due to multiple failed attempts. Please wait ${Math.ceil(lockoutDuration / 1000)} seconds.`,
						type: 'warning'
					}]);
				} else {
					// Show server errors or default message
					const msgs: FieldError[] = [];
					if (result.message) {
						msgs.push({ field: 'general', message: result.message, type: 'error' });
					}
					if (result.errors) {
						setServerFieldErrors(result.errors);
						Object.entries(result.errors).forEach(([field, arr]) => {
							arr.forEach(m => msgs.push({ field, message: m, type: 'error' }));
						});
					}
					if (msgs.length === 0) {
						msgs.push({ field: 'general', message: "Invalid agent code or password.", type: 'error' });
					}
					setFieldErrors(msgs);
				}
			}
		} catch (error) {
			setFieldErrors([{
				field: 'general',
				message: "Login failed due to a network or server error. Please check your connection and try again.",
				type: 'error'
			}]);
		} finally {
			setIsSubmitting(false);
		}
	};

	// Enhanced field error display
	const getFieldError = (fieldName: string): FieldError | undefined => {
		return fieldErrors.find(error => error.field === fieldName);
	};

	// Enhanced error message component
	const ErrorMessage = ({ error }: { error: FieldError }) => (
		<motion.div
			initial={{ opacity: 0, y: -10, height: 0 }}
			animate={{ opacity: 1, y: 0, height: "auto" }}
			exit={{ opacity: 0, y: -10, height: 0 }}
			className={`flex items-start gap-2 p-3 rounded-lg text-sm ${
				error.type === 'error' ? 'bg-red-50 border border-red-200 text-red-700' :
				error.type === 'warning' ? 'bg-yellow-50 border border-yellow-200 text-yellow-700' :
				'bg-blue-50 border border-blue-200 text-blue-700'
			}`}
		>
			{error.type === 'error' ? <AlertCircle className="w-4 h-4 mt-0.5 flex-shrink-0" /> :
			 error.type === 'warning' ? <AlertCircle className="w-4 h-4 mt-0.5 flex-shrink-0" /> :
			 <Info className="w-4 h-4 mt-0.5 flex-shrink-0" />}
			<span>{error.message}</span>
		</motion.div>
	);

	return (
		<>
			{isLoading && <LoadingOverlay text="Authenticating..." />}

			<PageTransition>
				<div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6 bg-gradient-to-br from-blue-50 via-white to-blue-50">
					<div className="w-full max-w-5xl xl:max-w-6xl bg-white rounded-2xl sm:rounded-3xl shadow-xl sm:shadow-2xl border border-gray-100 overflow-hidden relative">
						{/* Enhanced background effects */}
						<div className="absolute inset-0 bg-gradient-to-br from-blue-50/30 to-blue-50/30 pointer-events-none" />
						<div className="absolute top-0 right-0 w-20 h-20 sm:w-32 sm:h-32 bg-gradient-to-br from-blue-100/20 to-transparent rounded-full -translate-y-8 sm:-translate-y-16 translate-x-8 sm:translate-x-16" />
						<div className="absolute bottom-0 left-0 w-16 h-16 sm:w-24 sm:h-24 bg-gradient-to-tr from-blue-100/20 to-transparent rounded-full translate-y-6 sm:translate-y-12 -translate-x-6 sm:-translate-x-12" />
						
						<div className="grid grid-cols-1 md:grid-cols-2 relative">
							{/* Enhanced Left Section */}
							<FadeIn delay={0.2}>
								<motion.div
									initial={{ opacity: 0, x: -20 }}
									animate={{ opacity: 1, x: 0 }}
									transition={{ duration: 0.8, ease: [0.25, 0.46, 0.45, 0.94] }}
									className="flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 h-full bg-gradient-to-br from-blue-50/50 to-blue-50/50 relative overflow-hidden"
								>
									{/* Enhanced floating icons */}
									<motion.div 
										animate={{ y: [-10, 10, -10] }} 
										transition={{ duration: 6, repeat: Infinity, ease: "easeInOut" }} 
										className="absolute top-4 sm:top-8 right-4 sm:right-8 text-blue-200/40 sm:text-blue-200/60"
									>
										<Shield size={20} className="sm:w-6 sm:h-6" />
									</motion.div>
									<motion.div 
										animate={{ y: [10, -10, 10] }} 
										transition={{ duration: 8, repeat: Infinity, ease: "easeInOut" }} 
										className="absolute bottom-4 sm:bottom-8 left-4 sm:left-8 text-blue-200/40 sm:text-blue-200/60"
									>
										<Users size={20} className="sm:w-6 sm:h-6" />
									</motion.div>
									
									<div className="text-center space-y-3 sm:space-y-4">
										<div className="flex justify-center">
											<Image 
												src="/logo.png" 
												alt="WeKongsi" 
												width={520} 
												height={280} 
												priority 
												className="w-[120px] sm:w-[160px] md:w-[200px] lg:w-[280px] xl:w-[320px] h-auto drop-shadow-lg" 
											/>
										</div>
										<motion.div 
											initial={{ opacity: 0, y: 20 }} 
											animate={{ opacity: 1, y: 0 }} 
											transition={{ delay: 0.8, duration: 0.6 }} 
											className="space-y-2"
										>
											<h3 className="text-base sm:text-lg md:text-xl lg:text-2xl font-semibold text-gray-800">
												Welcome Back!
											</h3>
											<p className="text-gray-600 text-xs sm:text-sm md:text-base max-w-xs">
												Access your insurance management dashboard with secure authentication
											</p>
										</motion.div>
										
										{/* Enhanced security features display */}
										<motion.div 
											initial={{ opacity: 0, y: 20 }} 
											animate={{ opacity: 1, y: 0 }} 
											transition={{ delay: 1.0, duration: 0.6 }} 
											className="flex items-center justify-center gap-4 text-xs text-gray-500"
										>
											<div className="flex items-center gap-1">
												<Lock className="w-3 h-3" />
												<span>256-bit SSL</span>
											</div>
											<div className="flex items-center gap-1">
												<Shield className="w-3 h-3" />
												<span>Secure Auth</span>
											</div>
										</motion.div>
									</div>
								</motion.div>
							</FadeIn>
							
							{/* Enhanced Right Section - Login Form */}
							<SlideUp delay={0.4}>
								<motion.div 
									initial={{ opacity: 0, x: 20 }} 
									animate={{ opacity: 1, x: 0 }} 
									transition={{ duration: 0.8, ease: [0.25, 0.46, 0.45, 0.94] }} 
									className="flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 bg-white"
								>
									<div className="w-full max-w-sm sm:max-w-md">
										<ScaleIn delay={0.6}>
											<div className="text-center mb-6 sm:mb-8">
												<div className="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 mx-auto mb-3 sm:mb-4 bg-gradient-to-br from-[#264EE4] to-[#264EE4] rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg">
													<Shield className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-white" />
												</div>
												<h2 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800 mb-2">
													Login Now
												</h2>
												<p className="text-gray-600 text-xs sm:text-sm md:text-base">
													Please enter the details below to continue
												</p>
											</div>
										</ScaleIn>

										{/* Enhanced Success Message */}
										<AnimatePresence>
											{successMsg && (
												<motion.div 
													initial={{ opacity: 0, y: -10, scale: 0.95 }}
													animate={{ opacity: 1, y: 0, scale: 1 }}
													exit={{ opacity: 0, y: -10, scale: 0.95 }}
													className="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-2 shadow-sm"
												>
													<CheckCircle2 className="w-4 h-4" />
													{successMsg}
												</motion.div>
											)}
										</AnimatePresence>

										{/* Enhanced Error Messages */}
										<AnimatePresence>
											{(fieldErrors.length > 0 || Object.keys(serverFieldErrors).length > 0) && (
												<motion.div 
													initial={{ opacity: 0, y: -10, height: 0 }}
													animate={{ opacity: 1, y: 0, height: "auto" }}
													exit={{ opacity: 0, y: -10, height: 0 }}
													className="mb-6 space-y-2"
												>
													{/* Show only unique field errors */}
													{fieldErrors
														.filter((error, index, self) => 
															index === self.findIndex(e => e.field === error.field)
														)
														.map((error, index) => (
															<ErrorMessage key={`e-${index}`} error={error} />
														))}
													
													{/* Show server field errors */}
													{Object.entries(serverFieldErrors).map(([field, msgs]) => (
														<div key={field} className="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
															<div className="flex items-start gap-2">
																<Info className="w-4 h-4 mt-0.5 flex-shrink-0" />
																<div>
																	<div className="font-semibold capitalize">
																		{field.replace(/_/g, ' ')}
																	</div>
																	<ul className="list-disc ml-5 mt-1">
																		{msgs.map((m, i) => (<li key={`${field}-${i}`}>{m}</li>))}
																	</ul>
																</div>
															</div>
														</div>
													))}
												</motion.div>
											)}
										</AnimatePresence>

									{/* Login Mode Toggle */}
									<FadeIn delay={0.7}>
										<div className="flex items-center justify-center gap-1 bg-gray-100 rounded-lg p-1 mb-6">
											<button
												type="button"
												onClick={() => {
													setLoginMode('agent_code');
													setFormData({ agent_code_suffix: '', password: formData.password });
													setFieldErrors([]);
												}}
												className={`flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 ${
													loginMode === 'agent_code' 
														? 'bg-white text-[#264EE4] shadow-sm' 
														: 'text-gray-600 hover:text-gray-800'
												}`}
											>
												Agent Code
											</button>
											<button
												type="button"
												onClick={() => {
													setLoginMode('email');
													setFormData({ agent_code_suffix: '', password: formData.password });
													setFieldErrors([]);
												}}
												className={`flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 ${
													loginMode === 'email' 
														? 'bg-white text-[#264EE4] shadow-sm' 
														: 'text-gray-600 hover:text-gray-800'
												}`}
											>
												Email
											</button>
										</div>
									</FadeIn>

									<form className="space-y-4 sm:space-y-6" onSubmit={handleLogin}>
										{/* Enhanced Login Field */}
										<FadeIn delay={0.8}>
												<div className="space-y-3">
													<label className="text-sm font-semibold text-gray-700 flex items-center gap-2">
														<span className="w-2 h-2 bg-[#264EE4] rounded-full" />
														{loginMode === 'email' ? 'Email Address' : 'Agent Code'}
													</label>
													
													{loginMode === 'agent_code' ? (
														<div className="flex gap-2 sm:gap-3">
															<input 
																value="AGT" 
																disabled 
																className="w-16 h-10 sm:h-12 rounded-lg sm:rounded-xl border-2 border-gray-200 bg-gray-50 text-center text-gray-600 font-semibold text-xs sm:text-sm shadow-sm" 
															/>
															<div className="flex-1 relative">
																<input 
																	name="agent_code_suffix" 
																	value={formData.agent_code_suffix} 
																	onChange={handleInputChange} 
																	placeholder="00001" 
																	maxLength={5} 
																	pattern="[0-9]*" 
																	inputMode="numeric" 
																	className={`w-full h-10 sm:h-12 rounded-lg sm:rounded-xl border-2 px-3 sm:px-4 focus:outline-none focus:ring-2 focus:ring-[#264EE4]/20 transition-all duration-300 text-xs sm:text-sm font-medium shadow-sm hover:border-gray-300 tracking-widest ${
																		getFieldError('agent_code_suffix') ? 'border-red-300 focus:border-red-400 focus:ring-red-400/20' : 'border-gray-200 focus:border-[#264EE4]'
																	}`}
																/>
																{formData.agent_code_suffix.length === 5 && !getFieldError('agent_code_suffix') && (
																	<motion.div 
																		initial={{ opacity: 0, scale: 0 }}
																		animate={{ opacity: 1, scale: 1 }}
																		className="absolute right-3 top-1/2 -translate-y-1/2 text-green-500"
																	>
																		<CheckCircle2 className="w-4 h-4" />
																	</motion.div>
																)}
															</div>
														</div>
													) : (
														<div className="relative">
															<input 
																name="agent_code_suffix" 
																type="email"
																value={formData.agent_code_suffix} 
																onChange={handleInputChange} 
																placeholder="agent@khholdings.com" 
																className={`w-full h-10 sm:h-12 rounded-lg sm:rounded-xl border-2 px-3 sm:px-4 focus:outline-none focus:ring-2 focus:ring-[#264EE4]/20 transition-all duration-300 text-xs sm:text-sm font-medium shadow-sm hover:border-gray-300 ${
																	getFieldError('agent_code_suffix') ? 'border-red-300 focus:border-red-400 focus:ring-red-400/20' : 'border-gray-200 focus:border-[#264EE4]'
																}`}
															/>
															{formData.agent_code_suffix.includes('@') && !getFieldError('agent_code_suffix') && (
																<motion.div 
																	initial={{ opacity: 0, scale: 0 }}
																	animate={{ opacity: 1, scale: 1 }}
																	className="absolute right-3 top-1/2 -translate-y-1/2 text-green-500"
																>
																	<CheckCircle2 className="w-4 h-4" />
																</motion.div>
															)}
														</div>
													)}
													{/* Field-specific error */}
													<AnimatePresence>
														{getFieldError('agent_code_suffix') && (
															<motion.div 
																initial={{ opacity: 0, height: 0 }}
																animate={{ opacity: 1, height: "auto" }}
																exit={{ opacity: 0, height: 0 }}
																className="text-red-600 text-xs flex items-center gap-1"
															>
																<AlertCircle className="w-3 h-3" />
																{getFieldError('agent_code_suffix')?.message}
															</motion.div>
														)}
													</AnimatePresence>
												</div>
											</FadeIn>

											{/* Enhanced Password Field */}
											<FadeIn delay={1.0}>
												<div className="space-y-3">
													<label className="text-sm font-semibold text-gray-700 flex items-center gap-2">
														<span className="w-2 h-2 bg-[#264EE4] rounded-full" />
														Password
													</label>
													<div className="relative">
														<input 
															name="password" 
															value={formData.password} 
															onChange={handleInputChange} 
															placeholder="Enter your password" 
															type={showPassword ? "text" : "password"} 
															className={`w-full h-10 sm:h-12 rounded-lg sm:rounded-xl border-2 px-3 sm:px-4 pr-10 sm:pr-12 focus:outline-none focus:ring-2 focus:ring-[#264EE4]/20 transition-all duration-300 text-xs sm:text-sm font-medium shadow-sm hover:border-gray-300 ${
																getFieldError('password') ? 'border-red-300 focus:border-red-400 focus:ring-red-400/20' : 'border-gray-200 focus:border-[#264EE4]'
															}`}
														/>
														<button 
															type="button" 
															onClick={() => setShowPassword(!showPassword)} 
															className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
														>
															{showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
														</button>
													</div>
													{/* Field-specific error */}
													<AnimatePresence>
														{getFieldError('password') && (
															<motion.div 
																initial={{ opacity: 0, height: 0 }}
																animate={{ opacity: 1, height: "auto" }}
																exit={{ opacity: 0, height: 0 }}
																className="text-red-600 text-xs flex items-center gap-1"
															>
																<AlertCircle className="w-3 h-3" />
																{getFieldError('password')?.message}
															</motion.div>
														)}
													</AnimatePresence>
												</div>
											</FadeIn>

											{/* Enhanced Forgot Password Link */}
											<FadeIn delay={1.2}>
												<div className="flex items-center justify-end">
													<Link 
														href="/forgot-password" 
														className="text-sm text-[#264EE4] hover:text-[#264EE4]/80 hover:underline transition-colors font-medium flex items-center gap-1 group"
													>
														<Lock className="w-3 h-3 group-hover:scale-110 transition-transform" />
														Forgot Password?
													</Link>
												</div>
											</FadeIn>

											{/* Enhanced Login Button */}
											<FadeIn delay={1.4}>
												<Button 
													type="submit" 
													className={`w-full h-10 sm:h-12 text-sm sm:text-base font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 ${
														isFormValid 
															? 'bg-gradient-to-r from-[#264EE4] to-[#264EE4] hover:from-[#264EE4]/90 hover:to-[#264EE4]/90' 
															: 'bg-gray-400 cursor-not-allowed hover:from-gray-400 hover:to-gray-400'
													}`}
													loading={isSubmitting} 
													loadingText="Authenticating..."
													disabled={!isFormValid || isSubmitting}
												>
													<div className="flex items-center justify-center gap-2">
														<Zap className="w-4 h-4" />
														Login
													</div>
												</Button>
											</FadeIn>
										</form>

										{/* Enhanced Footer */}
										<motion.div 
											initial={{ opacity: 0 }} 
											animate={{ opacity: 1 }} 
											transition={{ delay: 1.6, duration: 0.6 }} 
											className="mt-6 sm:mt-8 text-center"
										>
											<p className="text-xs text-gray-500 flex items-center justify-center gap-1">
												<Shield className="w-3 h-3" />
												Secure login powered by WeKongsi Insurance Platform
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



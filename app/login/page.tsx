"use client";

import { motion } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import { Button } from "../(ui)/components/ui";
import { useRouter } from "next/navigation";

export default function LoginPage() {
	const router = useRouter();

	const handleLogin = () => {
		// Simulate login process
		setTimeout(() => {
			router.push("/dashboard");
		}, 1000);
	};

	return (
		<div className="min-h-screen flex items-center justify-center p-4">
			<div className="w-full max-w-6xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
				<div className="grid grid-cols-1 md:grid-cols-2">
					{/* Left Section - Logo */}
					<motion.div
						initial={{ opacity: 0, x: -20 }}
						animate={{ opacity: 1, x: 0 }}
						transition={{ duration: 0.6 }}
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
					
					{/* Right Section - Login Form */}
					<motion.div
						initial={{ opacity: 0, x: 20 }}
						animate={{ opacity: 1, x: 0 }}
						transition={{ duration: 0.6, delay: 0.1 }}
						className="flex items-center justify-center p-8 lg:p-12"
					>
						<div className="w-full max-w-md">
							<h2 className="text-2xl sm:text-3xl font-semibold text-center mb-2">Login Now</h2>
							<p className="text-gray-600 text-center mb-8">Please enter the details below to continue</p>

							<form className="space-y-6" onSubmit={(e) => { e.preventDefault(); handleLogin(); }}>
								<div className="space-y-2">
									<label className="text-sm text-gray-700 font-medium">Phone Number</label>
									<div className="flex gap-2">
										<input 
											value="+60" 
											disabled 
											className="w-16 h-12 rounded-lg border border-gray-200 bg-gray-50 text-center text-gray-600" 
										/>
										<input 
											placeholder="Phone Number" 
											className="flex-1 h-12 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition-colors" 
										/>
									</div>
								</div>
								<div className="space-y-2">
									<label className="text-sm text-gray-700 font-medium">Password</label>
									<input 
										placeholder="Password" 
										type="password" 
										className="w-full h-12 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition-colors" 
									/>
								</div>
								<div className="flex items-center justify-end text-sm">
									<Link href="#" className="text-emerald-600 hover:underline hover:text-emerald-700 transition-colors">
										Forgot Password ?
									</Link>
								</div>
								<Button type="submit" className="w-full h-12 text-base font-medium">
									Login
								</Button>
							</form>
						</div>
					</motion.div>
				</div>
			</div>
		</div>
	);
}



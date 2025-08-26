"use client";

import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { PageTransition, FadeIn, StaggeredContainer, StaggeredItem } from "../(ui)/components/PageTransition";
import { Phone, Building2, Stethoscope } from "lucide-react";

export default function HospitalsPage() {
	return (
		<PageTransition>
			<div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6 bg-gradient-to-br from-blue-50/30 via-white to-emerald-50/30">
				<div className="w-full max-w-4xl xl:max-w-6xl green-gradient-border p-3 sm:p-4 md:p-6 lg:p-8 xl:p-10">
					<div className="flex flex-col gap-4 sm:gap-6 md:gap-8 lg:gap-10">
						{/* Header Section */}
						<StaggeredContainer className="flex flex-col items-center text-center gap-3 sm:gap-4 md:gap-6">
							<StaggeredItem>
								<motion.div
									initial={{ scale: 0.8, opacity: 0 }}
									animate={{ scale: 1, opacity: 1 }}
									transition={{ duration: 0.6, ease: "easeOut" }}
									className="relative"
								>
									<Image 
										src="/assets/emas_logo.png" 
										alt="eMAS" 
										width={220} 
										height={100} 
										className="w-28 h-auto sm:w-32 md:w-44 lg:w-56 xl:w-64 drop-shadow-lg" 
									/>
									<motion.div
										animate={{ 
											scale: [1, 1.05, 1],
											opacity: [0.3, 0.6, 0.3]
										}}
										transition={{ 
											duration: 3, 
											repeat: Infinity, 
											ease: "easeInOut" 
										}}
										className="absolute inset-0 w-full h-full bg-blue-400/20 rounded-full blur-xl -z-10"
									/>
								</motion.div>
							</StaggeredItem>
							
							<StaggeredItem>
								<FadeIn delay={0.3}>
									<h2 className="text-lg sm:text-xl md:text-2xl lg:text-3xl font-bold text-gray-800 leading-tight px-4">
										List of All Panel Hospitals & Clinics{" "}
										<span className="bg-gradient-to-r from-blue-600 to-emerald-600 bg-clip-text text-transparent">
											thru eMAS
										</span>
									</h2>
								</FadeIn>
							</StaggeredItem>
							
							<StaggeredItem>
								<motion.div 
									initial={{ opacity: 0, y: 20 }}
									animate={{ opacity: 1, y: 0 }}
									transition={{ delay: 0.5, duration: 0.6 }}
									className="flex items-center gap-2 text-xs sm:text-sm text-gray-600 bg-blue-50 px-3 py-2 rounded-full border border-blue-200"
								>
									<Phone size={14} className="text-blue-600" />
									<span className="font-medium">eMAS Hotline:</span>
									<span className="text-blue-700 font-semibold">03 9213 0103</span>
								</motion.div>
							</StaggeredItem>
						</StaggeredContainer>
						
						{/* Cards Grid */}
						<StaggeredContainer className="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 md:gap-6 w-full max-w-3xl xl:max-w-4xl mt-2 sm:mt-4">
							{/* Panel Hospitals Card */}
							<StaggeredItem>
								<Link 
									href="/hospitals/list" 
									className="block"
								>
									<motion.div 
										className="rounded-xl border border-blue-100 bg-white p-4 sm:p-6 md:p-8 lg:p-10 hover:shadow-xl transition-all duration-300 hover:border-blue-300 group relative overflow-hidden"
										whileHover={{ 
											scale: 1.02,
											y: -5
										}}
										transition={{ type: "spring", stiffness: 300, damping: 20 }}
									>
										{/* Background decoration */}
										<motion.div
											className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100/30 to-transparent rounded-full -translate-y-10 translate-x-10"
											animate={{ 
												scale: [1, 1.2, 1],
												opacity: [0.3, 0.6, 0.3]
											}}
											transition={{ 
												duration: 4, 
												repeat: Infinity, 
												ease: "easeInOut" 
											}}
										/>
										
										<div className="text-center relative z-10">
											<motion.div 
												className="w-10 h-10 sm:w-12 sm:h-12 md:w-14 sm:h-14 lg:w-16 lg:h-16 mx-auto mb-2 sm:mb-3 md:mb-4 flex items-center justify-center text-blue-600 group-hover:text-blue-700 transition-colors duration-300"
												whileHover={{ rotate: 5, scale: 1.1 }}
												transition={{ type: "spring", stiffness: 300, damping: 20 }}
											>
												<Building2 size={32} className="sm:w-[36px] sm:h-[36px] md:w-[40px] md:h-[40px] lg:w-[44px] lg:h-[44px]" />
											</motion.div>
											<div className="font-bold text-gray-800 text-sm sm:text-base md:text-lg lg:text-xl group-hover:text-blue-700 transition-colors duration-300">
												Panel Hospitals
											</div>
											<div className="text-gray-500 text-xs sm:text-sm mt-1 group-hover:text-gray-600 transition-colors duration-300">
												More than 250 hospitals nationwide.
											</div>
											<motion.div
												className="mt-3 h-0.5 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full"
												initial={{ width: 0 }}
												whileHover={{ width: "100%" }}
												transition={{ duration: 0.3 }}
											/>
										</div>
									</motion.div>
								</Link>
							</StaggeredItem>
							
							{/* Panel Clinics Card */}
							<StaggeredItem>
								<Link href="/clinics" className="block">
									<motion.div 
										className="rounded-xl border border-blue-100 bg-white p-4 sm:p-6 md:p-8 lg:p-10 hover:shadow-xl transition-all duration-300 hover:border-blue-300 group relative overflow-hidden"
										whileHover={{ 
											scale: 1.02,
											y: -5
										}}
										transition={{ type: "spring", stiffness: 300, damping: 20 }}
									>
										{/* Background decoration */}
										<motion.div
											className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100/30 to-transparent rounded-full -translate-y-10 translate-x-10"
											animate={{ 
												scale: [1, 1.2, 1],
												opacity: [0.3, 0.6, 0.3]
											}}
											transition={{ 
												duration: 4, 
												repeat: Infinity, 
												ease: "easeInOut",
												delay: 1
											}}
										/>
										
										<div className="text-center relative z-10">
											<motion.div 
												className="w-10 h-10 sm:w-12 sm:h-12 md:w-14 sm:h-14 lg:w-16 lg:h-16 mx-auto mb-2 sm:mb-3 md:mb-4 flex items-center justify-center text-emerald-600 group-hover:text-emerald-700 transition-colors duration-300"
												whileHover={{ rotate: -5, scale: 1.1 }}
												transition={{ type: "spring", stiffness: 300, damping: 20 }}
											>
												<Stethoscope size={32} className="sm:w-[36px] sm:h-[36px] md:w-[40px] md:h-[40px] lg:w-[44px] lg:h-[44px]" />
											</motion.div>
											<div className="font-bold text-gray-800 text-sm sm:text-base md:text-lg lg:text-xl group-hover:text-emerald-700 transition-colors duration-300">
												Panel Clinics
											</div>
											<div className="text-gray-500 text-xs sm:text-sm mt-1 group-hover:text-gray-600 transition-colors duration-300">
												More than 4,000 clinics nationwide.
											</div>
											<motion.div
												className="mt-3 h-0.5 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-full"
												initial={{ width: 0 }}
												whileHover={{ width: "100%" }}
												transition={{ duration: 0.3 }}
											/>
										</div>
									</motion.div>
								</Link>
							</StaggeredItem>
						</StaggeredContainer>
					</div>
				</div>
			</div>
		</PageTransition>
	);
}



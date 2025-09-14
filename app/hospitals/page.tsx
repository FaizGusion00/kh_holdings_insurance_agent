"use client";

import Image from "next/image";
import Link from "next/link";
import { Phone, Building2, Stethoscope, ArrowRight } from "lucide-react";
import { PageTransition, StaggeredContainer, StaggeredItem, FadeIn } from "../(ui)/components/PageTransition";

export default function HospitalsPage() {
	return (
		<PageTransition>
			<div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-emerald-50">
				<div className="container mx-auto px-4 py-8 md:py-12 flex items-center justify-center">
					<div className="w-full max-w-4xl">
						{/* Header Section */}
						<StaggeredContainer>
							<StaggeredItem>
								<div className="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 md:p-8 lg:p-10">
									<div className="text-center mb-8 md:mb-10">
										<div className="mb-6">
											<Image 
												src="/assets/emas_logo.png" 
												alt="eMAS" 
												width={200} 
												height={80} 
												className="mx-auto drop-shadow-md" 
											/>
										</div>
										
										<h1 className="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800 mb-4 leading-tight">
											List of All Panel Hospitals & Clinics{" "}
											<span className="bg-gradient-to-r from-blue-600 to-emerald-600 bg-clip-text text-transparent">
												thru eMAS
											</span>
										</h1>
										
										<div className="inline-flex items-center gap-2 md:gap-3 text-gray-700 bg-blue-50 px-4 md:px-6 py-2 md:py-3 rounded-full border border-blue-200">
											<Phone size={16} className="text-blue-600" />
											<span className="font-medium text-sm md:text-base">eMAS Hotline:</span>
											<span className="text-blue-700 font-bold text-base md:text-lg">03 9213 0103</span>
										</div>
									</div>
									
									{/* Cards Grid - Compact and Centered */}
									<div className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
										{/* Panel Hospitals Card */}
										<StaggeredItem>
											<Link href="/hospitals/list" className="block group">
												<div className="bg-white rounded-xl border-2 border-blue-200 p-6 md:p-8 hover:shadow-2xl hover:border-blue-400 hover:-translate-y-1 transition-all duration-300 h-full relative overflow-hidden">
													{/* Background Pattern */}
													<div className="absolute inset-0 bg-gradient-to-br from-blue-50/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
													
													<div className="relative z-10 text-center">
														<div className="w-16 h-16 md:w-20 md:h-20 mx-auto mb-4 md:mb-6 flex items-center justify-center bg-blue-50 rounded-xl text-blue-600 group-hover:bg-blue-100 group-hover:scale-110 transition-all duration-300">
															<Building2 size={32} className="md:w-10 md:h-10" />
														</div>
														<h3 className="text-xl md:text-2xl font-bold text-gray-800 mb-2 md:mb-3 group-hover:text-blue-700 transition-colors duration-300">
															Panel Hospitals
														</h3>
														<p className="text-gray-600 text-sm md:text-base group-hover:text-gray-700 transition-colors duration-300 mb-8 md:mb-10">
															More than 250 hospitals nationwide.
														</p>
														
														{/* Interactive Button */}
														<div className="flex items-center justify-center gap-2 text-blue-600 group-hover:text-blue-700 transition-colors duration-300">
															<span className="text-sm font-medium">View Hospitals</span>
															<ArrowRight size={16} className="group-hover:translate-x-1 transition-transform duration-300" />
														</div>
														
													</div>
												</div>
											</Link>
										</StaggeredItem>
										
										{/* Panel Clinics Card */}
										<StaggeredItem>
											<Link href="/clinics" className="block group">
												<div className="bg-white rounded-xl border-2 border-emerald-200 p-6 md:p-8 hover:shadow-2xl hover:border-emerald-400 hover:-translate-y-1 transition-all duration-300 h-full relative overflow-hidden">
													{/* Background Pattern */}
													<div className="absolute inset-0 bg-gradient-to-br from-emerald-50/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
													
													<div className="relative z-10 text-center">
														<div className="w-16 h-16 md:w-20 md:h-20 mx-auto mb-4 md:mb-6 flex items-center justify-center bg-emerald-50 rounded-xl text-emerald-600 group-hover:bg-emerald-100 group-hover:scale-110 transition-all duration-300">
															<Stethoscope size={32} className="md:w-10 md:h-10" />
														</div>
														<h3 className="text-xl md:text-2xl font-bold text-gray-800 mb-2 md:mb-3 group-hover:text-emerald-700 transition-colors duration-300">
															Panel Clinics
														</h3>
														<p className="text-gray-600 text-sm md:text-base group-hover:text-gray-700 transition-colors duration-300 mb-8 md:mb-10">
															More than 4,000 clinics nationwide.
														</p>
														
														{/* Interactive Button */}
														<div className="flex items-center justify-center gap-2 text-emerald-600 group-hover:text-emerald-700 transition-colors duration-300">
															<span className="text-sm font-medium">View Clinics</span>
															<ArrowRight size={16} className="group-hover:translate-x-1 transition-transform duration-300" />
														</div>
													
													</div>
												</div>
											</Link>
										</StaggeredItem>
									</div>
									
									{/* Additional Info Section */}
									<FadeIn delay={0.4}>
										<div className="mt-6 md:mt-8 pt-6 md:pt-8 border-t border-gray-100">
											<div className="text-center">
												<p className="text-gray-600 text-sm md:text-base">
													Access our comprehensive network of healthcare providers across Malaysia
												</p>
												<div className="mt-3 flex items-center justify-center gap-4 text-xs text-gray-500">
													<span className="flex items-center gap-1">
														<div className="w-2 h-2 bg-blue-500 rounded-full"></div>
														Hospitals
													</span>
													<span className="flex items-center gap-1">
														<div className="w-2 h-2 bg-emerald-500 rounded-full"></div>
														Clinics
													</span>
												</div>
											</div>
										</div>
									</FadeIn>
								</div>
							</StaggeredItem>
						</StaggeredContainer>
					</div>
				</div>
			</div>
		</PageTransition>
	);
}



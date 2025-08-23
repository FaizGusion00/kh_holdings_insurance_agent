"use client";

import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";

export default function HospitalsPage() {
	return (
		<div className="min-h-screen flex items-center justify-center p-3 sm:p-4 lg:p-6">
			<div className="w-full max-w-4xl xl:max-w-6xl green-gradient-border p-4 sm:p-6 lg:p-10">
				<div className="flex flex-col gap-6 sm:gap-8 lg:gap-10">
					<div className="flex flex-col items-center text-center gap-4 sm:gap-6">
						<Image src="/emas.svg" alt="emas" width={220} height={100} className="w-32 h-auto sm:w-44 lg:w-56 xl:w-64" />
						<h2 className="text-xl sm:text-2xl lg:text-3xl font-semibold text-gray-800 leading-tight px-4">
							List of All Panel Hospitals & Clinics{" "}
							<span className="text-blue-600">thru eMAS</span>
						</h2>
						<div className="text-xs sm:text-sm text-gray-600">eMAS Hotline : 03 9213 0103</div>
						
						<div className="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 w-full max-w-3xl xl:max-w-4xl mt-2 sm:mt-4">
							<Link 
								href="/hospitals/list" 
								className="rounded-xl border border-blue-100 bg-white p-6 sm:p-8 lg:p-10 hover:shadow-md transition hover:border-blue-200"
							>
								<div className="text-center">
									<div className="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 mx-auto mb-3 sm:mb-4 flex items-center justify-center">
										<svg viewBox="0 0 64 64" className="w-full h-full">
											<rect x="8" y="8" width="48" height="48" rx="4" fill="none" stroke="currentColor" strokeWidth="2"/>
											<rect x="16" y="16" width="32" height="24" rx="2" fill="none" stroke="currentColor" strokeWidth="2"/>
											<rect x="20" y="20" width="8" height="8" rx="1" fill="none" stroke="currentColor" strokeWidth="2"/>
											<rect x="32" y="20" width="8" height="8" rx="1" fill="none" stroke="currentColor" strokeWidth="2"/>
											<rect x="20" y="32" width="8" height="8" rx="1" fill="none" stroke="currentColor" strokeWidth="2"/>
											<rect x="32" y="32" width="8" height="8" rx="1" fill="none" stroke="currentColor" strokeWidth="2"/>
											<rect x="44" y="44" width="8" height="8" rx="1" fill="none" stroke="currentColor" strokeWidth="2"/>
											<line x1="28" y1="44" x2="36" y2="44" stroke="currentColor" strokeWidth="2"/>
											<line x1="28" y1="48" x2="36" y2="48" stroke="currentColor" strokeWidth="2"/>
										</svg>
									</div>
									<div className="font-semibold text-gray-800 text-base sm:text-lg lg:text-xl">Panel Hospitals</div>
									<div className="text-gray-500 text-xs sm:text-sm mt-1">More than 250 hospitals nationwide.</div>
								</div>
							</Link>
							
							<div className="rounded-xl border border-blue-100 bg-white p-6 sm:p-8 lg:p-10 opacity-70">
								<div className="text-center">
									<div className="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 mx-auto mb-3 sm:mb-4 flex items-center justify-center">
										<svg viewBox="0 0 64 64" className="w-full h-full">
											<path d="M32 8C24.268 8 18 14.268 18 22c0 8 14 26 14 26s14-18 14-26c0-7.732-6.268-14-14-14z" fill="none" stroke="currentColor" strokeWidth="2"/>
											<circle cx="32" cy="22" r="6" fill="none" stroke="currentColor" strokeWidth="2"/>
										</svg>
									</div>
									<div className="font-semibold text-gray-800 text-base sm:text-lg lg:text-xl">Panel Clinics</div>
									<div className="text-gray-500 text-xs sm:text-sm mt-1">More than 4,000 hospitals nationwide.</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}



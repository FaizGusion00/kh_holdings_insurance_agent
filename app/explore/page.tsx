"use client";

import { motion } from "framer-motion";
import { Users, Handshake, FileText, Wrench, Target } from "lucide-react";

export default function ExplorePage() {
	const faqs = [
		{ text: "What is WeKongsi?", icon: Users },
		{ text: "Benefits Provided", icon: Handshake },
		{ text: "Waiting Period", icon: FileText },
		{ text: "Admission Procedure", icon: Wrench },
		{ text: "Sharing & Top Up Scenario", icon: Target },
	];

	return (
		<div className="min-h-screen flex items-center justify-center p-3 sm:p-4 lg:p-6">
			<div className="w-full max-w-4xl xl:max-w-6xl green-gradient-border p-4 sm:p-6 lg:p-10">
				<div className="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-10">
					<div className="space-y-4 sm:space-y-6">
						<div>
							<h2 className="text-2xl sm:text-3xl lg:text-4xl font-semibold text-gray-800 leading-tight">Explore We Kongsi</h2>
							<p className="text-gray-600 mt-2 text-sm sm:text-base">Now your health finances are in one place and always under control.</p>
							<div className="h-1 w-16 sm:w-20 bg-blue-600 rounded mt-3" />
						</div>
						<div className="flex flex-col gap-3 sm:gap-4">
							<button className="h-10 sm:h-12 px-4 sm:px-6 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition text-sm sm:text-base">
								Read Program Guideline
							</button>
							<button className="h-10 sm:h-12 px-4 sm:px-6 rounded-lg bg-blue-100 text-blue-800 font-medium hover:bg-blue-200 transition text-sm sm:text-base">
								Logs
							</button>
						</div>
					</div>
					<div className="space-y-2 sm:space-y-3">
						{faqs.map(({ text, icon: Icon }) => (
							<div key={text} className="rounded-lg border border-blue-100 bg-white hover:bg-blue-50 transition">
								<div className="h-12 sm:h-14 px-3 sm:px-4 flex items-center justify-between">
									<div className="flex items-center gap-2 sm:gap-3">
										<div className="w-6 h-6 sm:w-8 sm:h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
											<Icon size={14} className="sm:w-4 sm:h-4 text-blue-700" />
										</div>
										<span className="font-medium text-gray-800 text-sm sm:text-base">{text}</span>
									</div>
									<span className="text-blue-600 text-base sm:text-lg flex-shrink-0">▾</span>
								</div>
							</div>
						))}
					</div>
				</div>
				
			</div>
		</div>
	);
}



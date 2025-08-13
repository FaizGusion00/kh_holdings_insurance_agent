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
		<div className="min-h-screen flex items-center justify-center p-4">
			<div className="w-full max-w-6xl glass-panel p-6 sm:p-10 mint-outline">
				<div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
					<div className="space-y-6">
						<div>
							<h2 className="text-3xl font-semibold text-gray-800">Explore We Kongsi</h2>
							<p className="text-gray-600 mt-2">Now your health finances are in one place and always under control.</p>
							<div className="h-1 w-20 bg-emerald-500 rounded mt-3" />
						</div>
						<div className="flex flex-col gap-3">
							<button className="h-12 px-6 rounded-lg bg-emerald-500 text-white font-medium hover:bg-emerald-600 transition">
								Read Program Guideline
							</button>
							<button className="h-12 px-6 rounded-lg bg-emerald-100 text-emerald-800 font-medium hover:bg-emerald-200 transition">
								Logs
							</button>
						</div>
					</div>
					<div className="space-y-3">
						{faqs.map(({ text, icon: Icon }) => (
							<div key={text} className="rounded-lg border border-emerald-100 bg-white hover:bg-emerald-50 transition">
								<div className="h-14 px-4 flex items-center justify-between">
									<div className="flex items-center gap-3">
										<div className="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
											<Icon size={16} className="text-emerald-700" />
										</div>
										<span className="font-medium text-gray-800">{text}</span>
									</div>
									<span className="text-emerald-600 text-lg">▾</span>
								</div>
							</div>
						))}
					</div>
				</div>
				<div className="mt-12 flex justify-start">
					<div className="relative w-64 h-48">
						{/* Telescope illustration */}
						<div className="absolute bottom-0 left-0 w-48 h-32">
							{/* Person */}
							<div className="absolute bottom-0 left-4 w-8 h-12 bg-gray-300 rounded-full"></div>
							{/* Lab coat */}
							<div className="absolute bottom-8 left-2 w-12 h-8 bg-white border border-gray-200 rounded"></div>
							{/* Telescope */}
							<div className="absolute bottom-4 left-8 w-16 h-2 bg-emerald-400 rounded-full"></div>
							<div className="absolute bottom-6 left-12 w-2 h-8 bg-emerald-400 rounded-full"></div>
							<div className="absolute bottom-14 left-10 w-8 h-8 bg-emerald-400 rounded-full border-2 border-emerald-500"></div>
							{/* Plant */}
							<div className="absolute bottom-0 left-16 w-6 h-8 bg-emerald-600 rounded-t-full"></div>
							<div className="absolute bottom-6 left-14 w-10 h-6 bg-emerald-500 rounded-full"></div>
							{/* Planet */}
							<div className="absolute top-4 right-4 w-6 h-6 bg-blue-400 rounded-full"></div>
							{/* Stars */}
							<div className="absolute top-2 left-8 w-1 h-1 bg-white rounded-full"></div>
							<div className="absolute top-6 left-12 w-1 h-1 bg-white rounded-full"></div>
							<div className="absolute top-8 left-6 w-1 h-1 bg-white rounded-full"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}



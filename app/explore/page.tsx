"use client";

import { motion, AnimatePresence } from "framer-motion";
import { useState } from "react";
import { Users, Handshake, FileText, Wrench, Target, ChevronDown, BookOpen, FileText as LogsIcon } from "lucide-react";
import { PageTransition, FadeIn, StaggeredContainer, StaggeredItem } from "../(ui)/components/PageTransition";

export default function ExplorePage() {
	const [openIndex, setOpenIndex] = useState<number | null>(null);

	const faqs = [
		{
			text: "What is WeKongsi?",
			icon: Users,
			content: (
				<div className="text-gray-700 text-sm sm:text-base leading-relaxed space-y-3">
					<p>
						We Kongsi is a community sharing service for medical costs that offers a unique approach to managing healthcare needs. It is not an insurance or takaful, but rather a service provided by Kita Kongsi Sdn Bhd.
					</p>
					<p>
						Members are responsible for their own healthcare costs, but eligible costs may be shared by the community through a review process by our appointed Third-Party Administrator.
					</p>
					<p>
						For more information on the sharing procedure, please refer to the program guideline.
					</p>
				</div>
			),
		},
		{
			text: "Benefits Provided",
			icon: Handshake,
			content: (
				<ul className="list-disc pl-5 text-gray-700 text-sm sm:text-base space-y-2">
					<li>Take care on inpatient medical cost</li>
					<li>Outpatient cancer treatment</li>
					<li>Bereavement Payment</li>
					<li>Medical professional advice</li>
					<li>Health improvement activities</li>
					<li>Healthcare items discount</li>
					<li>Others with health related</li>
				</ul>
			),
		},
		{
			text: "Waiting Period",
			icon: FileText,
			content: (
				<ul className="list-disc pl-5 text-gray-700 text-sm sm:text-base space-y-2">
					<li>
						Immediate effect on accidental injury medical cost up to RM10,000 (Paid & Claim)
					</li>
					<li>
						90 days to unlock common illnesses such as cold, flu, fever, dengue, food poison, infection and etc
					</li>
					<li>
						180 days to unlock chronic illnesses such as diabetes, high blood pressure, heart attack, cancer, stroke and etc
					</li>
				</ul>
			),
		},
		{
			text: "Admission Procedure",
			icon: Wrench,
			content: (
				<ol className="list-decimal pl-5 text-gray-700 text-sm sm:text-base space-y-2">
					<li>Active Account</li>
					<li>Pass waiting period</li>
					<li>Feel sick and visit clinic hospital</li>
					<li>Get referral letter to hospital for further treatment/diagnosis</li>
					<li>Visit panel hospital</li>
					<li>Submit admission form</li>
					<li>Approve admission case by our TPA (eMAS)</li>
					<li>Receive Treatment</li>
					<li>Discharge and recover.</li>
				</ol>
			),
		},
		{
			text: "Sharing & Top Up Scenario",
			icon: Target,
			content: (
				<div className="text-gray-700 text-sm sm:text-base leading-relaxed space-y-3">
					<p>Top up an opening Sharing Account</p>
					<p>
						Sharing Account is used to share other member&apos;s medical cost. The sharing date will happen at every 7th of the month
					</p>
					<p>
						If community number got 5,000 and the month medical cost is RM100,000, then the medical cost will be shared by each member by paying RM20* (For age 45 or below)
					</p>
					<p>
						If the month does not have any medical case, then no sharing contribution needed for the month
					</p>
				</div>
			),
		},
	];

	return (
		<PageTransition>
			<div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6 bg-gradient-to-br from-blue-50/30 via-white to-emerald-50/30">
				<div className="w-full max-w-4xl xl:max-w-6xl green-gradient-border p-3 sm:p-4 md:p-6 lg:p-8 xl:p-10">
					<div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 md:gap-8 lg:gap-10">
						{/* Left Column - Header & Buttons */}
						<StaggeredContainer className="space-y-3 sm:space-y-4 md:space-y-6">
							<StaggeredItem>
								<div>
									<FadeIn delay={0.2}>
										<h2 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800 leading-tight bg-gradient-to-r from-gray-800 to-blue-700 bg-clip-text text-transparent">
											Explore We Kongsi
										</h2>
									</FadeIn>
									<motion.p 
										initial={{ opacity: 0, y: 20 }}
										animate={{ opacity: 1, y: 0 }}
										transition={{ delay: 0.4, duration: 0.6 }}
										className="text-gray-600 mt-2 text-sm sm:text-base"
									>
										Now your health finances are in one place and always under control.
									</motion.p>
									<motion.div 
										initial={{ width: 0 }}
										animate={{ width: "100%" }}
										transition={{ delay: 0.6, duration: 0.8 }}
										className="h-1 w-12 sm:w-16 md:w-20 bg-gradient-to-r from-blue-600 to-emerald-500 rounded-full mt-2 sm:mt-3"
									/>
								</div>
							</StaggeredItem>
							
							<StaggeredItem>
								<div className="flex flex-col gap-2 sm:gap-3 md:gap-4">
									<motion.button 
										className="h-9 sm:h-10 md:h-12 px-3 sm:px-4 md:px-6 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium hover:from-blue-700 hover:to-blue-800 transition-all duration-300 transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
										whileHover={{ scale: 1.02 }}
										whileTap={{ scale: 0.98 }}
									>
										<BookOpen size={16} className="sm:w-[18px] sm:h-[18px]" />
										Read Program Guideline
									</motion.button>
									<motion.button 
										className="h-9 sm:h-10 md:h-12 px-3 sm:px-4 md:px-6 rounded-lg bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 font-medium hover:from-blue-200 hover:to-blue-300 transition-all duration-300 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg flex items-center justify-center gap-2"
										whileHover={{ scale: 1.02 }}
										whileTap={{ scale: 0.98 }}
									>
										<LogsIcon size={16} className="sm:w-[18px] sm:h-[18px]" />
										Logs
									</motion.button>
								</div>
							</StaggeredItem>
						</StaggeredContainer>
						
						{/* Right Column - FAQ Accordion */}
						<StaggeredContainer className="space-y-2 sm:space-y-3">
							{faqs.map(({ text, icon: Icon, content }, idx) => {
								const isOpen = openIndex === idx;
								return (
									<StaggeredItem key={text}>
										<motion.div 
											className="rounded-lg border border-blue-100 bg-white transition-all duration-300 hover:shadow-md hover:border-blue-200"
											whileHover={{ scale: 1.01 }}
											transition={{ type: "spring", stiffness: 300, damping: 20 }}
										>
											<button
												onClick={() => setOpenIndex(isOpen ? null : idx)}
												className="w-full h-10 sm:h-12 md:h-14 px-3 sm:px-4 flex items-center justify-between text-left group"
												aria-expanded={isOpen}
											>
												<div className="flex items-center gap-2 sm:gap-3">
													<motion.div 
														className="w-5 h-5 sm:w-6 sm:h-6 md:w-8 md:h-8 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center flex-shrink-0 group-hover:from-blue-200 group-hover:to-blue-300 transition-all duration-300"
														whileHover={{ rotate: 5, scale: 1.1 }}
													>
														<Icon size={12} className="sm:w-[14px] sm:h-[14px] md:w-4 md:h-4 text-blue-700" />
													</motion.div>
													<span className="font-semibold text-gray-800 text-sm sm:text-base group-hover:text-blue-700 transition-colors duration-200">
														{text}
													</span>
												</div>
												<motion.div
													className="text-blue-600 text-base sm:text-lg flex-shrink-0 group-hover:text-blue-700 transition-colors duration-200"
													animate={{ rotate: isOpen ? 180 : 0 }}
													transition={{ duration: 0.3, ease: "easeInOut" }}
												>
													<ChevronDown size={20} />
												</motion.div>
											</button>
											<AnimatePresence initial={false}>
												{isOpen && (
													<motion.div
														initial={{ height: 0, opacity: 0 }}
														animate={{ height: "auto", opacity: 1 }}
														exit={{ height: 0, opacity: 0 }}
														transition={{ duration: 0.3, ease: "easeInOut" }}
														className="overflow-hidden"
													>
														<div className="px-3 sm:px-4 pb-2 sm:pb-3 md:pb-4 pt-1 sm:pt-2">
															<motion.div
																initial={{ opacity: 0, y: 10 }}
																animate={{ opacity: 1, y: 0 }}
																transition={{ delay: 0.1, duration: 0.3 }}
															>
																{content}
															</motion.div>
														</div>
													</motion.div>
												)}
											</AnimatePresence>
										</motion.div>
									</StaggeredItem>
								);
							})}
						</StaggeredContainer>
					</div>
				</div>
			</div>
		</PageTransition>
	);
}



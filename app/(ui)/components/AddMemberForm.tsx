"use client";

import Image from "next/image";
import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { ArrowLeft, Shield } from "lucide-react";

export interface NewMemberFormData {
	name: string;
	nric: string;
	confirmNric: string;
	phone: string;
	email: string;
	address: string;
	date_of_birth: string;
	gender: string;
	occupation: string;
	race: string;
	relationship_with_agent: string;
	emergency_contact_phone: string;
	emergency_contact_name: string;
	emergency_contact_relationship: string;
}

export function AddMemberForm({ onSubmit }: { onSubmit: (data: NewMemberFormData) => void }) {
	const [currentStep, setCurrentStep] = useState(1);
	const [form, setForm] = useState<NewMemberFormData>({ 
		name: "", 
		nric: "", 
		confirmNric: "", 
		phone: "",
		email: "",
		address: "",
		date_of_birth: "",
		gender: "male",
		occupation: "",
		race: "Malay", 
		relationship_with_agent: "Myself",
		emergency_contact_phone: "",
		emergency_contact_name: "",
		emergency_contact_relationship: "Myself"
	});

	const isStep1Valid = form.name && form.nric && form.confirmNric && form.nric === form.confirmNric && form.phone && form.email && form.address && form.date_of_birth && form.gender && form.occupation;
	const isStep2Valid = form.emergency_contact_phone && form.emergency_contact_name && form.emergency_contact_relationship;

	const handleContinue = () => {
		if (currentStep === 1 && isStep1Valid) {
			setCurrentStep(2);
		}
	};

	const handleBack = () => {
		if (currentStep === 2) {
			setCurrentStep(1);
		}
	};

	const handleCreateMember = () => {
		if (isStep2Valid) {
			onSubmit(form);
		}
	};

	return (
		<div className="space-y-6">
			{/* Progress Indicator */}
			<div className="flex items-center justify-center space-x-4 mb-6">
				<div className="flex items-center space-x-2">
					<div className={`w-3 h-3 rounded-full ${currentStep >= 1 ? 'bg-emerald-500' : 'bg-gray-300'}`} />
					<span className={`text-sm ${currentStep >= 1 ? 'text-emerald-600 font-medium' : 'text-gray-400'}`}>
						Basic Details
					</span>
				</div>
				<div className="w-8 h-0.5 bg-gray-300" />
				<div className="flex items-center space-x-2">
					<div className={`w-3 h-3 rounded-full ${currentStep >= 2 ? 'bg-emerald-500' : 'bg-gray-300'}`} />
					<span className={`text-sm ${currentStep >= 2 ? 'text-emerald-600 font-medium' : 'text-gray-400'}`}>
						Emergency Contact
					</span>
				</div>
			</div>

			<AnimatePresence mode="wait">
				{currentStep === 1 ? (
					<motion.div
						key="step1"
						initial={{ opacity: 0, x: 20 }}
						animate={{ opacity: 1, x: 0 }}
						exit={{ opacity: 0, x: -20 }}
						transition={{ duration: 0.3 }}
						className="grid grid-cols-1 md:grid-cols-[280px_1fr] gap-6 items-start"
					>
						{/* Left Side - Icon */}
						<div className="hidden md:flex items-center justify-center">
							<div className="relative">
								<Image 
									src="/assets/add_member.png" 
									alt="Add Member" 
									width={220} 
									height={220} 
									className="opacity-90" 
								/>
							</div>
						</div>

						{/* Right Side - Form */}
						<div className="space-y-4">
							<div>
								<h3 className="text-lg sm:text-xl font-semibold text-gray-800 mb-2">Basic Details</h3>
								<p className="text-gray-600 text-sm mb-4">
									Insert the basic details for the member. Please make sure all the details are correct.
								</p>
							</div>

							<div className="space-y-3">
								<div>
									<label className="text-sm font-medium text-gray-700 mb-1 block">Name per NRIC</label>
									<input 
										value={form.name} 
										onChange={(e) => setForm({...form, name: e.target.value})} 
										placeholder="Enter full name as per NRIC" 
										className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200" 
									/>
								</div>

								<div>
									<label className="text-sm font-medium text-gray-700 mb-1 block">NRIC Number</label>
									<input 
										value={form.nric} 
										onChange={(e) => setForm({...form, nric: e.target.value})} 
										placeholder="Enter NRIC number" 
										className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200" 
									/>
								</div>

								<div>
									<label className="text-sm font-medium text-gray-700 mb-1 block">Confirm NRIC Number</label>
									<input 
										value={form.confirmNric} 
										onChange={(e) => setForm({...form, confirmNric: e.target.value})} 
										placeholder="Re-enter NRIC number" 
										className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200" 
									/>
								</div>

								<div>
									<label className="text-sm font-medium text-gray-700 mb-1 block">Phone Number</label>
									<div className="flex gap-2">
										<div className="w-16 h-11 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center text-gray-600 font-medium text-sm">
											+60
										</div>
										<input 
											value={form.phone} 
											onChange={(e) => setForm({...form, phone: e.target.value})} 
											placeholder="Phone number" 
											className="flex-1 h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200" 
										/>
									</div>
								</div>

								<div>
									<label className="text-sm font-medium text-gray-700 mb-1 block">Email Address</label>
									<input 
										value={form.email} 
										onChange={(e) => setForm({...form, email: e.target.value})} 
										placeholder="Email address" 
										type="email"
										className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200" 
									/>
								</div>

								<div>
									<label className="text-sm font-medium text-gray-700 mb-1 block">Address</label>
									<textarea 
										value={form.address} 
										onChange={(e) => setForm({...form, address: e.target.value})} 
										placeholder="Full address" 
										rows={3}
										className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200 resize-none" 
									/>
								</div>

								<div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
									<div>
										<label className="text-sm font-medium text-gray-700 mb-1 block">Date of Birth</label>
										<input 
											value={form.date_of_birth} 
											onChange={(e) => setForm({...form, date_of_birth: e.target.value})} 
											type="date"
											className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200" 
										/>
									</div>
									<div>
										<label className="text-sm font-medium text-gray-700 mb-1 block">Gender</label>
										<select 
											value={form.gender} 
											onChange={(e) => setForm({...form, gender: e.target.value})} 
											className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
										>
											<option value="male">Male</option>
											<option value="female">Female</option>
										</select>
									</div>
								</div>

								<div>
									<label className="text-sm font-medium text-gray-700 mb-1 block">Occupation</label>
									<input 
										value={form.occupation} 
										onChange={(e) => setForm({...form, occupation: e.target.value})} 
										placeholder="Occupation" 
										className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200" 
										/>
								</div>

								<div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
									<div>
										<label className="text-sm font-medium text-gray-700 mb-1 block">Race</label>
										<select 
											value={form.race} 
											onChange={(e) => setForm({...form, race: e.target.value})} 
											className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
										>
											<option value="Malay">Malay</option>
											<option value="Chinese">Chinese</option>
											<option value="Indian">Indian</option>
											<option value="Others">Others</option>
										</select>
									</div>
									<div>
										<label className="text-sm font-medium text-gray-700 mb-1 block">Relationship with Agent</label>
										<select 
											value={form.relationship_with_agent} 
											onChange={(e) => setForm({...form, relationship_with_agent: e.target.value})} 
											className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
										>
											<option value="Myself">Myself</option>
											<option value="Spouse">Spouse</option>
											<option value="Child">Child</option>
											<option value="Parent">Parent</option>
											<option value="Other">Other</option>
										</select>
									</div>
								</div>

								<button 
									disabled={!isStep1Valid} 
									onClick={handleContinue} 
									className="w-full h-11 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-medium disabled:opacity-50 hover:from-emerald-600 hover:to-emerald-700 transition-all duration-200 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg"
								>
									Continue
								</button>
							</div>
						</div>
					</motion.div>
				) : (
					<motion.div
						key="step2"
						initial={{ opacity: 0, x: 20 }}
						animate={{ opacity: 1, x: 0 }}
						exit={{ opacity: 0, x: -20 }}
						transition={{ duration: 0.3 }}
						className="grid grid-cols-1 md:grid-cols-[280px_1fr] gap-6 items-start"
					>
						{/* Left Side - Emergency Contact Icon */}
						<div className="hidden md:flex items-center justify-center">
							<div className="relative">
								<div className="w-32 h-32 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-full flex items-center justify-center shadow-xl">
									<Shield className="w-16 h-16 text-white" />
								</div>
								<div className="absolute inset-0 w-32 h-32 bg-teal-400/20 rounded-full animate-pulse" />
							</div>
						</div>

						{/* Right Side - Emergency Contact Form */}
						<div className="space-y-4">
							<div>
								<h3 className="text-lg sm:text-xl font-semibold text-gray-800 mb-2">Emergency Contact</h3>
								<p className="text-gray-600 text-sm mb-4">
									We will reach out to the emergency contact in case of emergency.
								</p>
							</div>

							<div className="space-y-3">
								<div>
									<label className="text-sm font-medium text-gray-700 mb-1 block">Emergency Contact Phone Number</label>
									<div className="flex gap-2">
										<div className="w-16 h-11 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center text-gray-600 font-medium text-sm">
											+60
										</div>
										<input 
											value={form.emergency_contact_phone} 
											onChange={(e) => setForm({...form, emergency_contact_phone: e.target.value})} 
											placeholder="Emergency Contact Phone Number" 
											className="flex-1 h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 transition-all duration-200" 
										/>
									</div>
								</div>

								<div>
									<label className="text-sm font-medium text-gray-700 mb-1 block">Emergency Contact Name</label>
									<input 
										value={form.emergency_contact_name} 
										onChange={(e) => setForm({...form, emergency_contact_name: e.target.value})} 
										placeholder="Emergency Contact Name" 
										className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 transition-all duration-200" 
									/>
								</div>

								<div>
									<label className="text-sm font-medium text-emerald-600 mb-1 block">Emergency Contact Relationship</label>
									<select 
										value={form.emergency_contact_relationship} 
										onChange={(e) => setForm({...form, emergency_contact_relationship: e.target.value})} 
										className="w-full h-11 rounded-lg border border-gray-200 px-3 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 transition-all duration-200"
									>
										<option value="Myself">Myself</option>
										<option value="Spouse">Spouse</option>
										<option value="Child">Child</option>
										<option value="Parent">Parent</option>
										<option value="Sibling">Sibling</option>
										<option value="Friend">Friend</option>
										<option value="Other">Other</option>
									</select>
								</div>

								<div className="flex gap-3 pt-2">
									<button 
										onClick={handleBack} 
										className="flex-1 h-11 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-all duration-200 flex items-center justify-center gap-2"
									>
										<ArrowLeft size={16} />
										Back
									</button>
									<button 
										disabled={!isStep2Valid} 
										onClick={handleCreateMember} 
										className="flex-1 h-11 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-medium disabled:opacity-50 hover:from-emerald-600 hover:to-emerald-700 transition-all duration-200 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg"
									>
										Create Member
									</button>
								</div>
							</div>
						</div>
					</motion.div>
				)}
			</AnimatePresence>
		</div>
	);
}



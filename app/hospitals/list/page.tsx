"use client";

import { useState } from "react";
import { Search } from "lucide-react";
import HospitalsPage from "../page";

// Re-export the list layout only: we copy markup from the bottom half of hospitals page for simplicity
export default function HospitalsListPage() {
	// import states/hospitals mock directly here to keep the page static
	const states = ["Johor", "Kedah", "Kelantan", "Melaka", "Negeri Sembilan", "Pahang", "Perak", "Perlis", "Pulau Pinang", "Sabah", "Sarawak", "Selangor"];
	const hospitals = [
		{ name: "COLUMBIA ASIA HOSPITAL - ISKANDAR PUTERI", address: "Persiaran Afiat, Taman Kesihatan Afiat, 79250 Nusajaya, 79250 Johor", phone: "07-2339999" },
		{ name: "HOSPITAL PENAWAR SDN BHD", address: "No. 15 19, Business Center, 81700, Pasir Gudang, 81700 Johor", phone: "07-2521800" },
		{ name: "KENSINGTON GREEN SPECIALIST CENTRE SDN BHD", address: "No 2 Jln Ceria, Tmn Nusa Indah, 79100, Iskandar Puteri, 79100 Johor", phone: "07-2133899" },
	];

	const [searchQuery, setSearchQuery] = useState("");

	// Filter hospitals based on search query
	const filteredHospitals = hospitals.filter(hospital =>
		hospital.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
		hospital.address.toLowerCase().includes(searchQuery.toLowerCase()) ||
		hospital.phone.includes(searchQuery)
	);

	return (
		<div className="min-h-screen flex items-center justify-center p-4">
			<div className="w-full max-w-6xl glass-panel p-6 sm:p-10 mint-outline">
				<div className="flex flex-col gap-6">
					<h2 className="text-xl sm:text-2xl font-semibold">All hospitals</h2>
					
					{/* Search Box */}
					<div className="relative">
						<div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
							<Search className="h-5 w-5 text-gray-400" />
						</div>
						<input
							type="text"
							placeholder="Search hospitals by name, address, or phone..."
							value={searchQuery}
							onChange={(e) => setSearchQuery(e.target.value)}
							className="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition-colors"
						/>
					</div>

					<div className="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6">
						<div className="space-y-3">
							<div className="rounded-lg border border-emerald-100">
								<div className="bg-gray-50 px-4 py-2 font-medium">Private Hospital</div>
								<div className="px-4 py-2 bg-gray-50 border-t border-emerald-100 text-sm">Johor</div>
								<div className="divide-y">
									{filteredHospitals.length > 0 ? (
										filteredHospitals.map((h, i) => (
											<div key={i} className="p-4">
												<div className="font-semibold">{h.name}</div>
												<div className="text-gray-600 text-sm mt-1">{h.address}</div>
												<div className="text-gray-600 text-sm mt-2">{h.phone}</div>
											</div>
										))
									) : (
										<div className="p-4 text-center text-gray-500">
											No hospitals found matching your search.
										</div>
									)}
								</div>
							</div>
						</div>
						<div className="space-y-3">
							<div className="rounded-lg border border-emerald-100">
								<div className="bg-gray-50 px-4 py-2 font-medium">Government Hospital</div>
								<div className="p-3 space-y-2">
									{states.map((s) => (
										<div key={s} className="h-10 rounded-md bg-gray-50 border border-gray-200 px-3 flex items-center text-sm">
											{s}
										</div>
									))}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}



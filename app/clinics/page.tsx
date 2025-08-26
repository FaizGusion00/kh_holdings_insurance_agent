"use client";

import { useState } from "react";
import { Search } from "lucide-react";

export default function ClinicsPage() {
	const states = [
		"All",
		"Johor",
		"Kedah",
		"Kelantan",
		"Melaka",
		"Negeri Sembilan",
		"Pahang",
		"Perak",
		"Perlis",
		"Pulau Pinang",
		"Sabah",
		"Sarawak",
		"Selangor",
	];

	const clinics = [
		{ name: "KLINIK KESIHATAN BANDAR BARU UDA", address: "Jalan Padi Emas 1, Bandar Baru Uda, 81200 Johor Bahru, Johor", phone: "07-2223344", state: "Johor" },
		{ name: "POLIKLINIK PERMAI", address: "No. 2, Jalan Sutera, Taman Sentosa, 80150 Johor Bahru, Johor", phone: "07-3345566", state: "Johor" },
		{ name: "KLINIK MEDIVIRON TAMAN UNIVERSITI", address: "No. 5, Jalan Kebudayaan, Taman Universiti, 81300 Skudai, Johor", phone: "07-5567788", state: "Johor" },
		{ name: "KLINIK KESIHATAN AMPANG", address: "Jalan Memanda 3, Bandar Baru Ampang, 68000 Ampang, Selangor", phone: "03-42534455", state: "Selangor" },
		{ name: "KLINIK PERGIGIAN IPOH", address: "Jalan Hospital, 30450 Ipoh, Perak", phone: "05-2531122", state: "Perak" },
	];

	const [searchQuery, setSearchQuery] = useState("");
	const [selectedState, setSelectedState] = useState<string>("All");
	const [page, setPage] = useState(1);
	const [pageSize, setPageSize] = useState(10);

	const filteredClinics = clinics.filter((clinic) => {
		const matchesState = selectedState === "All" || clinic.state === selectedState;
		const q = searchQuery.toLowerCase();
		const matchesSearch =
			clinic.name.toLowerCase().includes(q) ||
			clinic.address.toLowerCase().includes(q) ||
			clinic.phone.includes(searchQuery) ||
			clinic.state.toLowerCase().includes(q);
		return matchesState && matchesSearch;
	});

	const totalItems = filteredClinics.length;
	const totalPages = Math.max(1, Math.ceil(totalItems / pageSize));
	const currentPage = Math.min(page, totalPages);
	const startIndex = (currentPage - 1) * pageSize;
	const pageItems = filteredClinics.slice(startIndex, startIndex + pageSize);

	return (
		<div className="min-h-screen flex items-center justify-center p-3 sm:p-4 lg:p-6">
			<div className="w-full max-w-4xl xl:max-w-6xl green-gradient-border p-4 sm:p-6 lg:p-10">
				<div className="flex flex-col gap-4 sm:gap-6">
					<h2 className="text-lg sm:text-xl lg:text-2xl font-semibold">All clinics</h2>

					{/* Top filters */}
					<div className="grid grid-cols-1 md:grid-cols-[1fr_220px] gap-3">
						<div className="relative">
							<div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
								<Search className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
							</div>
							<input
								type="text"
								placeholder="Search by name, address, phone or state..."
								value={searchQuery}
								onChange={(e) => { setSearchQuery(e.target.value); setPage(1); }}
								className="block w-full pl-10 pr-3 py-2.5 sm:py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300 transition-colors text-sm sm:text-base"
							/>
						</div>
						<div className="flex items-center gap-2">
							<label className="text-sm text-gray-600">Per page</label>
							<select
								value={pageSize}
								onChange={(e) => { setPageSize(parseInt(e.target.value)); setPage(1); }}
								className="w-full md:w-auto border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300"
							>
								<option value={5}>5</option>
								<option value={10}>10</option>
								<option value={20}>20</option>
							</select>
						</div>
					</div>

					<div className="grid grid-cols-1 lg:grid-cols-[1fr_300px] xl:grid-cols-[1fr_340px] gap-4 sm:gap-6">
						<div className="space-y-2 sm:space-y-3">
							<div className="rounded-lg border border-blue-100">
								<div className="bg-gray-50 px-3 sm:px-4 py-2 font-medium text-sm sm:text-base">Private Clinics</div>
								<div className="px-3 sm:px-4 py-2 bg-gray-50 border-t border-blue-100 text-xs sm:text-sm">{selectedState === "All" ? "All Malaysia" : selectedState}</div>
								<div className="divide-y">
									{pageItems.length > 0 ? (
										pageItems.map((c, i) => (
											<div key={i} className="p-3 sm:p-4">
												<div className="font-semibold text-sm sm:text-base leading-tight">{c.name}</div>
												<div className="text-gray-600 text-xs sm:text-sm mt-1 leading-relaxed">{c.address}</div>
												<div className="text-gray-600 text-xs sm:text-sm mt-2">{c.phone}</div>
												<div className="text-gray-500 text-[11px] sm:text-xs mt-1">State: {c.state}</div>
											</div>
										))
									) : (
										<div className="p-3 sm:p-4 text-center text-gray-500 text-sm sm:text-base">
											No clinics found matching your search.
										</div>
									)}
								</div>
								{/* Pagination */}
								<div className="flex items-center justify-between px-3 sm:px-4 py-2 border-t border-blue-100 bg-white">
									<div className="text-xs sm:text-sm text-gray-600">Page {currentPage} of {totalPages} • {totalItems} results</div>
									<div className="flex items-center gap-2">
										<button
											onClick={() => setPage((p) => Math.max(1, p - 1))}
											disabled={currentPage === 1}
											className="px-3 py-1.5 text-sm rounded border border-gray-200 disabled:opacity-50"
										>
											Prev
										</button>
										<button
											onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
											disabled={currentPage === totalPages}
											className="px-3 py-1.5 text-sm rounded border border-gray-200 disabled:opacity-50"
										>
											Next
										</button>
									</div>
								</div>
							</div>
						</div>
						<div className="space-y-2 sm:space-y-3">
							<div className="rounded-lg border border-blue-100">
								<div className="bg-gray-50 px-3 sm:px-4 py-2 font-medium text-sm sm:text-base">Filter by state</div>
								<div className="p-2 sm:p-3 space-y-2 max-h-[60vh] overflow-auto">
									{states.map((s) => (
										<button
											key={s}
											onClick={() => { setSelectedState(s); setPage(1); }}
											className={`h-8 sm:h-10 w-full rounded-md border px-2 sm:px-3 flex items-center justify-between text-xs sm:text-sm transition ${
												s === selectedState ? "bg-blue-100 border-blue-300 text-blue-800" : "bg-gray-50 border-gray-200 hover:bg-white"
											}`}
										>
											<span>{s}</span>
											{selectedState === s && <span className="text-[10px] sm:text-xs">Selected</span>}
										</button>
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



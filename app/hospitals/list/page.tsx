"use client";

import { useEffect, useMemo, useState } from "react";
import { Search } from "lucide-react";
import { apiService, HealthcareFacility } from "@/app/services/api";

// Hospitals list page connected to API
export default function HospitalsListPage() {
	const [allHospitals, setAllHospitals] = useState<HealthcareFacility[]>([]);
	const [isLoading, setIsLoading] = useState<boolean>(false);
	const [error, setError] = useState<string>("");

	const [searchQuery, setSearchQuery] = useState("");
	const [selectedState, setSelectedState] = useState<string>("All");
	const [page, setPage] = useState(1);
	const [pageSize, setPageSize] = useState(10);

	useEffect(() => {
		let mounted = true;
		async function load() {
			setIsLoading(true);
			setError("");
			try {
				const res = await apiService.getHospitals();
				if (mounted) {
					if (res.success && res.data) {
						setAllHospitals(res.data);
					} else {
						setError(res.message || "Failed to load hospitals");
					}
				}
			} catch (e) {
				if (mounted) setError(e instanceof Error ? e.message : "Failed to load hospitals");
			} finally {
				if (mounted) setIsLoading(false);
			}
		}
		load();
		return () => { mounted = false; };
	}, []);

	const states = useMemo(() => {
		const set = new Set<string>();
		allHospitals.forEach((h) => { if (h.state) set.add(h.state); });
		return ["All", ...Array.from(set).sort()];
	}, [allHospitals]);

	const filteredHospitals = useMemo(() => {
		const q = searchQuery.toLowerCase();
		return allHospitals.filter((hospital) => {
			const matchesState = selectedState === "All" || hospital.state === selectedState;
			const matchesSearch =
				(hospital.name || "").toLowerCase().includes(q) ||
				(hospital.address || "").toLowerCase().includes(q) ||
				(hospital.phone || "").includes(searchQuery) ||
				(hospital.state || "").toLowerCase().includes(q);
			return matchesState && matchesSearch;
		});
	}, [allHospitals, selectedState, searchQuery]);

	const totalItems = filteredHospitals.length;
	const totalPages = Math.max(1, Math.ceil(totalItems / pageSize));
	const currentPage = Math.min(page, totalPages);
	const startIndex = (currentPage - 1) * pageSize;
	const pageItems = filteredHospitals.slice(startIndex, startIndex + pageSize);

	return (
		<div className="min-h-screen flex items-center justify-center p-3 sm:p-4 lg:p-6">
			<div className="w-full max-w-4xl xl:max-w-6xl green-gradient-border p-4 sm:p-6 lg:p-10">
				<div className="flex flex-col gap-4 sm:gap-6">
					<h2 className="text-lg sm:text-xl lg:text-2xl font-semibold">All hospitals</h2>
					
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
								<div className="bg-gray-50 px-3 sm:px-4 py-2 font-medium text-sm sm:text-base">Private Hospital</div>
								<div className="px-3 sm:px-4 py-2 bg-gray-50 border-t border-blue-100 text-xs sm:text-sm">{selectedState === "All" ? "All Malaysia" : selectedState}</div>
								<div className="divide-y">
									{isLoading ? (
										<div className="p-3 sm:p-4 text-center text-gray-500 text-sm sm:text-base">Loading hospitals...</div>
									) : pageItems.length > 0 ? (
										pageItems.map((h, i) => (
											<div key={`${h.name}-${i}`} className="p-3 sm:p-4">
												<div className="font-semibold text-sm sm:text-base leading-tight">{h.name}</div>
												<div className="text-gray-600 text-xs sm:text-sm mt-1 leading-relaxed">{h.address}</div>
												<div className="text-gray-600 text-xs sm:text-sm mt-2">{h.phone || '-'}</div>
												<div className="text-gray-500 text-[11px] sm:text-xs mt-1">State: {h.state || '-'}</div>
											</div>
										))
									) : (
										<div className="p-3 sm:p-4 text-center text-gray-500 text-sm sm:text-base">
											No hospitals found.
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



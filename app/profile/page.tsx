"use client";

import { useRouter } from "next/navigation";
import { LogOut } from "lucide-react";

export default function ProfilePage() {
	const router = useRouter();

	const handleLogout = () => {
		// Simulate logout process
		setTimeout(() => {
			router.push("/login");
		}, 500);
	};

	return (
		<div className="min-h-screen flex items-center justify-center p-3 sm:p-4 lg:p-6">
			<div className="w-full max-w-4xl xl:max-w-6xl glass-panel p-4 sm:p-6 lg:p-10 kh-outline">
				{/* Logout Button - Top Right */}
				<div className="absolute top-3 sm:top-6 right-3 sm:right-6">
					<button
						onClick={handleLogout}
						className="flex items-center gap-2 px-3 sm:px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg border border-red-200 transition-colors duration-200"
					>
						<LogOut size={14} className="sm:w-4 sm:h-4" />
						<span className="text-xs sm:text-sm font-medium">Logout</span>
					</button>
				</div>

				<div className="grid grid-cols-1 md:grid-cols-[240px_1fr] lg:grid-cols-[260px_1fr] gap-4 sm:gap-6 lg:gap-8">
					<div className="space-y-2 sm:space-y-3">
						<div className="rounded-full w-12 h-12 sm:w-14 sm:h-14 bg-blue-600 text-white grid place-content-center text-lg sm:text-xl font-semibold">N</div>
						<div className="font-semibold text-sm sm:text-base leading-tight">NOR ZAKIAH BINT...</div>
						<div className="text-gray-500 text-xs sm:text-sm">+60192131100</div>
						<div className="mt-3 sm:mt-4 space-y-2">
							<div className="h-8 sm:h-10 rounded-lg bg-blue-600/90 text-white grid place-content-center text-xs sm:text-sm">My Profile</div>
							<div className="h-8 sm:h-10 rounded-lg bg-white border border-blue-100 grid place-content-center text-xs sm:text-sm">Payment Settings</div>
							<div className="h-8 sm:h-10 rounded-lg bg-white border border-blue-100 grid place-content-center text-xs sm:text-sm">Payment History</div>
							<div className="h-8 sm:h-10 rounded-lg bg-white border border-blue-100 grid place-content-center text-xs sm:text-sm">Referrer</div>
						</div>
					</div>
					<div className="space-y-4 sm:space-y-6">
						<h2 className="text-lg sm:text-xl font-semibold">My Profile</h2>
						<div className="rounded-lg border border-blue-100">
							<div className="bg-gray-50 px-3 sm:px-4 py-2 font-medium text-sm sm:text-base">General Info</div>
							<div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 p-3 sm:p-4">
								<div>
									<div className="text-gray-500 text-xs sm:text-sm">Name</div>
									<div className="font-medium text-sm sm:text-base leading-tight">NOR ZAKIAH BINTI WAN OMAR</div>
								</div>
								<div>
									<div className="text-gray-500 text-xs sm:text-sm">Agent Number</div>
									<div className="font-medium text-sm sm:text-base">KH-001234</div>
								</div>
								<div>
									<div className="text-gray-500 text-xs sm:text-sm">Phone Number</div>
									<div className="font-medium text-sm sm:text-base">+60192131100</div>
								</div>
							</div>
						</div>
						<div className="rounded-lg border border-blue-100">
							<div className="bg-gray-50 px-3 sm:px-4 py-2 font-medium text-sm sm:text-base">Address Info</div>
							<div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 p-3 sm:p-4">
								<div>
									<div className="text-gray-500 text-xs sm:text-sm">Address</div>
									<div className="font-medium text-sm sm:text-base leading-tight">NO 27A JALAN SG 1/6 TAMAN SRI GOMBAK</div>
								</div>
								<div>
									<div className="text-gray-500 text-xs sm:text-sm">Postal Code</div>
									<div className="font-medium text-sm sm:text-base">68100</div>
								</div>
								<div>
									<div className="text-gray-500 text-xs sm:text-sm">State</div>
									<div className="font-medium text-sm sm:text-base">Selangor</div>
								</div>
								<div>
									<div className="text-gray-500 text-xs sm:text-sm">City</div>
									<div className="font-medium text-sm sm:text-base">BATU CAVES</div>
								</div>
							</div>
						</div>
						<div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
							<button className="h-8 sm:h-10 px-3 sm:px-4 rounded-lg border border-blue-200 text-xs sm:text-sm">Update Profile</button>
							<button className="h-8 sm:h-10 px-3 sm:px-4 rounded-lg border border-blue-200 text-xs sm:text-sm">Change Phone Number</button>
							<button className="h-8 sm:h-10 px-3 sm:px-4 rounded-lg border border-blue-200 text-xs sm:text-sm">Change Password</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}



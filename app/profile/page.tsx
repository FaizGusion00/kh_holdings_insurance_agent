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
		<div className="min-h-screen flex items-center justify-center p-4">
			<div className="w-full max-w-6xl glass-panel p-6 sm:p-10 mint-outline">
				{/* Logout Button - Top Right */}
				<div className="absolute top-6 right-6">
					<button
						onClick={handleLogout}
						className="flex items-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg border border-red-200 transition-colors duration-200"
					>
						<LogOut size={16} />
						<span className="text-sm font-medium">Logout</span>
					</button>
				</div>

				<div className="grid grid-cols-1 md:grid-cols-[260px_1fr] gap-8">
					<div className="space-y-2">
						<div className="rounded-full w-14 h-14 bg-emerald-600 text-white grid place-content-center text-xl font-semibold">N</div>
						<div className="font-semibold">NOR ZAKIAH BINT...</div>
						<div className="text-gray-500 text-sm">+60192131100</div>
						<div className="mt-4 space-y-2">
							<div className="h-10 rounded-lg bg-emerald-600/90 text-white grid place-content-center text-sm">My Profile</div>
							<div className="h-10 rounded-lg bg-white border border-emerald-100 grid place-content-center text-sm">Payment Settings</div>
							<div className="h-10 rounded-lg bg-white border border-emerald-100 grid place-content-center text-sm">Payment History</div>
							<div className="h-10 rounded-lg bg-white border border-emerald-100 grid place-content-center text-sm">Referrer</div>
						</div>
					</div>
					<div className="space-y-6">
						<h2 className="text-xl font-semibold">My Profile</h2>
						<div className="rounded-lg border border-emerald-100">
							<div className="bg-gray-50 px-4 py-2 font-medium">General Info</div>
							<div className="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
								<div>
									<div className="text-gray-500 text-sm">Name</div>
									<div className="font-medium">NOR ZAKIAH BINTI WAN OMAR</div>
								</div>
								<div>
									<div className="text-gray-500 text-sm">Phone Number</div>
									<div className="font-medium">+60192131100</div>
								</div>
							</div>
						</div>
						<div className="rounded-lg border border-emerald-100">
							<div className="bg-gray-50 px-4 py-2 font-medium">Address Info</div>
							<div className="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
								<div>
									<div className="text-gray-500 text-sm">Address</div>
									<div className="font-medium">NO 27A JALAN SG 1/6 TAMAN SRI GOMBAK</div>
								</div>
								<div>
									<div className="text-gray-500 text-sm">Postal Code</div>
									<div className="font-medium">68100</div>
								</div>
								<div>
									<div className="text-gray-500 text-sm">State</div>
									<div className="font-medium">Selangor</div>
								</div>
								<div>
									<div className="text-gray-500 text-sm">City</div>
									<div className="font-medium">BATU CAVES</div>
								</div>
							</div>
						</div>
						<div className="flex gap-3">
							<button className="h-10 px-4 rounded-lg border border-emerald-200">Update Profile</button>
							<button className="h-10 px-4 rounded-lg border border-emerald-200">Change Phone Number</button>
							<button className="h-10 px-4 rounded-lg border border-emerald-200">Change Password</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}



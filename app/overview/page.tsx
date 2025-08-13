export default function OverviewPage() {
	return (
		<div className="min-h-screen flex items-center justify-center p-4">
			<div className="w-full max-w-6xl glass-panel p-6 sm:p-10 mint-outline">
				<div className="grid grid-cols-1 md:grid-cols-[260px_1fr] gap-8">
					<div className="space-y-3">
						<div className="rounded-full w-14 h-14 bg-emerald-600 text-white grid place-content-center text-xl font-semibold">N</div>
						<div className="font-semibold">NOR ZAKIAH BINT...</div>
						<div className="text-gray-500 text-sm">+60192131100</div>
						<div className="h-10 rounded-lg bg-emerald-600/90 text-white grid place-content-center text-sm">Overview</div>
					</div>
					<div className="space-y-6">
						<h2 className="font-semibold">Account Overview</h2>
						<div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div className="rounded-xl bg-emerald-400 text-white p-5">
								<div className="text-sm opacity-90">Total Member</div>
								<div className="text-3xl font-semibold mt-1">2</div>
							</div>
							<div className="rounded-xl bg-emerald-100 p-5">
								<div className="text-sm text-emerald-700">Total Referral</div>
								<div className="text-3xl font-semibold text-emerald-900 mt-1">1170</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}



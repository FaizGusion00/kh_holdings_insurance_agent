export default function OverviewPage() {
	return (
		<div className="min-h-screen flex items-center justify-center p-3 sm:p-4 lg:p-6">
			<div className="w-full max-w-4xl xl:max-w-6xl glass-panel p-4 sm:p-6 lg:p-10 kh-outline">
				<div className="grid grid-cols-1 md:grid-cols-[240px_1fr] lg:grid-cols-[260px_1fr] gap-4 sm:gap-6 lg:gap-8">
					<div className="space-y-2 sm:space-y-3">
						<div className="rounded-full w-12 h-12 sm:w-14 sm:h-14 bg-blue-600 text-white grid place-content-center text-lg sm:text-xl font-semibold">N</div>
						<div className="font-semibold text-sm sm:text-base leading-tight">NOR ZAKIAH BINTI ABU JAHAL</div>
						<div className="text-gray-500 text-xs sm:text-sm">+60192131100</div>
						<div className="mt-3 sm:mt-4 space-y-2">
							<div className="h-8 sm:h-10 rounded-lg bg-blue-600/90 text-white grid place-content-center text-xs sm:text-sm">Overview</div>
						</div>
					</div>
					<div className="space-y-4 sm:space-y-6">
						<h2 className="font-semibold text-lg sm:text-xl">Account Overview</h2>
						<div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
							<div className="rounded-xl bg-blue-500 text-white p-4 sm:p-5">
								<div className="text-xs sm:text-sm opacity-90">Total Member</div>
								<div className="text-2xl sm:text-3xl font-semibold mt-1">2</div>
							</div>
							<div className="rounded-xl bg-blue-100 p-4 sm:p-5">
								<div className="text-xs sm:text-sm text-blue-700">Total Referral</div>
								<div className="text-2xl sm:text-3xl font-semibold text-blue-900 mt-1">1170</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}



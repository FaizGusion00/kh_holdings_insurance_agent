"use client";

import {
	CircleHelp,
	Plus,
} from "lucide-react";
import { PageTransition, StaggeredContainer, StaggeredItem, FadeIn } from "../(ui)/components/PageTransition";

type Member = { name: string; nric: string; balance: number; status: string; initial: string; color: string };

const mockMembers: Member[] = [
	{ name: "KHAIRUL HAFIFZ BIN KHAIRUL OMAR KUMAR", nric: "851201145835", balance: 81.44, status: "Active", initial: "K", color: "bg-blue-800" },
	{ name: "NOR ZAKIAH BINTI WAN OMAR", nric: "820510085336", balance: 81.44, status: "Active", initial: "N", color: "bg-amber-600" },
];

function StatCard({ title, value, highlight, withButton }: { title: string; value: string; highlight?: boolean; withButton?: boolean }) {
	return (
		<div className={`rounded-2xl p-4 sm:p-6 ${highlight ? "bg-blue-50" : "bg-blue-50/60"}`}>
			<div className="flex items-start justify-between">
				<div>
					<div className="text-gray-700 font-medium text-sm sm:text-base">{title}</div>
					<div className={`mt-2 text-xl sm:text-2xl lg:text-3xl font-semibold ${highlight ? "text-blue-700" : "text-blue-700"}`}>{value}</div>
				</div>
				{title === "Total Member" && (
					<CircleHelp className="text-blue-600 opacity-70 w-4 h-4 sm:w-5 sm:h-5" />
				)}
			</div>
			{withButton && (
				<button className="mt-4 h-8 sm:h-9 px-3 sm:px-4 rounded-lg bg-blue-600 text-white text-xs sm:text-sm hover:bg-blue-700 transition-colors">Add Member</button>
			)}
		</div>
	);
}

function MemberCard({ m }: { m: Member }) {
	return (
		<div className="rounded-xl border border-blue-100 bg-white p-3 sm:p-4 flex items-start gap-3 sm:gap-4 shadow-sm">
			<div className={`w-10 h-10 sm:w-12 sm:h-12 rounded-full grid place-content-center text-white font-semibold text-sm sm:text-base flex-shrink-0 ${m.color}`}>{m.initial}</div>
			<div className="flex-1 space-y-1 min-w-0">
				<div className="text-xs sm:text-sm"><span className="font-semibold">Name</span> : <span className="truncate block">{m.name}</span></div>
				<div className="text-gray-600 text-xs sm:text-sm">NRIC : {m.nric}</div>
				<div className="text-gray-600 text-xs sm:text-sm">Balance : RM {m.balance.toFixed(2)}</div>
			</div>
			<span className="ml-2 rounded-full bg-blue-100 text-blue-700 px-2 py-0.5 text-[10px] sm:text-xs font-semibold leading-none flex-shrink-0">{m.status}</span>
		</div>
	);
}

export default function DashboardPage() {
	return (
		<PageTransition>
			<div className="min-h-screen flex items-center justify-center p-3 sm:p-4 lg:p-6">
				<div className="relative w-full max-w-4xl xl:max-w-6xl glass-panel p-4 sm:p-6 lg:p-10 kh-outline">
					{/* Profile Badge */}
					<FadeIn delay={0.3}>
						<div className="absolute right-3 sm:right-6 top-3 sm:top-6 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-rose-200 text-rose-900 grid place-content-center font-semibold text-sm sm:text-base">N</div>
					</FadeIn>
					
					<div className="flex flex-col gap-6 sm:gap-8">
						{/* Header Section */}
						<FadeIn delay={0.4}>
							<div>
								<p className="text-gray-500 text-sm sm:text-base">Hello</p>
								<h1 className="text-xl sm:text-2xl lg:text-3xl font-semibold leading-tight">NOR ZAKIAH BINTI ABU JAHAL</h1>
							</div>
						</FadeIn>
						
						<div className="grid grid-cols-1 lg:grid-cols-[1fr_1fr] xl:grid-cols-[380px_1fr] gap-4 sm:gap-6">
							{/* Left Column - Stats */}
							<StaggeredContainer className="grid grid-cols-1 gap-3 sm:gap-4">
								<StaggeredItem>
									<StatCard title="Total Member" value="4,138" highlight withButton />
								</StaggeredItem>
								<StaggeredItem>
									<div className="rounded-2xl p-4 sm:p-6 bg-blue-50">
										<div className="text-gray-700 font-medium text-sm sm:text-base">Shared Amount</div>
										<div className="text-xl sm:text-2xl lg:text-3xl font-semibold text-blue-700 mt-2">RM 1,114,938.94</div>
									</div>
								</StaggeredItem>
								<StaggeredItem>
									<StatCard title="Supported Hospitals" value="271" />
								</StaggeredItem>
								<StaggeredItem>
									<StatCard title="Supported Clinics" value="4,513" />
								</StaggeredItem>
							</StaggeredContainer>
							
							{/* Right Column - Member List */}
							<FadeIn delay={0.6}>
								<div className="flex flex-col gap-3 sm:gap-4">
									<div className="flex items-center justify-between">
										<div>
											<div className="font-semibold text-sm sm:text-base">My Member List</div>
											<div className="h-1 w-16 sm:w-20 bg-blue-600 rounded mt-2" />
										</div>
										<button className="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-blue-600 text-white grid place-content-center hover:bg-blue-700 transition-colors">
											<Plus size={16} className="sm:w-[18px] sm:h-[18px]" />
										</button>
									</div>
									<StaggeredContainer className="grid gap-3 sm:gap-4">
										{mockMembers.map((m, i) => (
											<StaggeredItem key={i}>
												<MemberCard m={m} />
											</StaggeredItem>
										))}
									</StaggeredContainer>
								</div>
							</FadeIn>
						</div>
					</div>
				</div>
			</div>
		</PageTransition>
	);
}



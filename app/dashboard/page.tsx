"use client";

import { motion } from "framer-motion";
import {
	CircleHelp,
	Plus,
} from "lucide-react";

type Member = { name: string; nric: string; balance: number; status: string; initial: string; color: string };

const mockMembers: Member[] = [
	{ name: "KHAIRUL HAFIFZ BIN KHAIRUL OMAR KUMAR", nric: "851201145835", balance: 81.44, status: "Active", initial: "K", color: "bg-emerald-800" },
	{ name: "NOR ZAKIAH BINTI WAN OMAR", nric: "820510085336", balance: 81.44, status: "Active", initial: "N", color: "bg-amber-600" },
];

function StatCard({ title, value, highlight, withButton }: { title: string; value: string; highlight?: boolean; withButton?: boolean }) {
	return (
		<div className={`rounded-2xl p-6 ${highlight ? "bg-emerald-50" : "bg-emerald-50/60"}`}>
			<div className="flex items-start justify-between">
				<div>
					<div className="text-gray-700 font-medium">{title}</div>
					<div className={`mt-2 text-2xl sm:text-3xl font-semibold ${highlight ? "text-emerald-700" : "text-emerald-700"}`}>{value}</div>
				</div>
				{title === "Total Member" && (
					<CircleHelp className="text-emerald-600 opacity-70" size={20} />
				)}
			</div>
			{withButton && (
				<button className="mt-4 h-9 px-4 rounded-lg bg-emerald-500 text-white text-sm">Add Member</button>
			)}
		</div>
	);
}

function MemberCard({ m }: { m: Member }) {
	return (
		<div className="rounded-xl border border-emerald-100 bg-white p-4 flex items-start gap-4 shadow-sm">
			<div className={`w-12 h-12 rounded-full grid place-content-center text-white font-semibold ${m.color}`}>{m.initial}</div>
			<div className="flex-1 space-y-1">
				<div className="text-sm"><span className="font-semibold">Name</span> : {m.name}</div>
				<div className="text-gray-600 text-sm">NRIC : {m.nric}</div>
				<div className="text-gray-600 text-sm">Balance : RM {m.balance.toFixed(2)}</div>
			</div>
			<span className="ml-2 rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-[12px] font-semibold leading-none">{m.status}</span>
		</div>
	);
}

export default function DashboardPage() {
	return (
		<div className="min-h-screen flex items-center justify-center p-4">
			<div className="relative w-full max-w-6xl glass-panel p-6 sm:p-10 mint-outline">
				<div className="absolute right-6 top-6 w-10 h-10 rounded-full bg-rose-200 text-rose-900 grid place-content-center font-semibold">N</div>
				<div className="flex flex-col gap-8">
					<div>
						<p className="text-gray-500">Hello</p>
						<h1 className="text-2xl sm:text-3xl font-semibold">NOR ZAKIAH BINT...</h1>
					</div>
					<div className="grid grid-cols-1 md:grid-cols-[380px_1fr] gap-6">
						<div className="grid grid-cols-1 gap-4">
							<motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }}>
								<StatCard title="Total Member" value="4,138" highlight withButton />
							</motion.div>
							<motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.05 }}>
								<div className="rounded-2xl p-6 bg-emerald-50">
									<div className="text-gray-700 font-medium">Shared Amount</div>
									<div className="text-2xl sm:text-3xl font-semibold text-emerald-700 mt-2">RM 1,114,938.94</div>
								</div>
							</motion.div>
							<motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }}>
								<StatCard title="Supported Hospitals" value="271" />
							</motion.div>
							<motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.15 }}>
								<StatCard title="Supported Clinics" value="4,513" />
							</motion.div>
						</div>
						<div className="flex flex-col gap-4">
							<div className="flex items-center justify-between">
								<div>
									<div className="font-semibold">My Member List</div>
									<div className="h-1 w-20 bg-emerald-500 rounded mt-2" />
								</div>
								<button className="w-9 h-9 rounded-full bg-emerald-500 text-white grid place-content-center">
									<Plus size={18} />
								</button>
							</div>
							<div className="grid gap-4">
								{mockMembers.map((m, i) => (
									<motion.div key={i} initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.06 }}>
										<MemberCard m={m} />
									</motion.div>
								))}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}



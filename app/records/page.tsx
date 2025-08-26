"use client";

import { useMemo, useState } from "react";
import { motion } from "framer-motion";
import { Calendar, Activity, Users, CheckCircle2, FileText, DollarSign } from "lucide-react";
import {
	ResponsiveContainer,
	AreaChart,
	Area,
	CartesianGrid,
	Tooltip,
	XAxis,
	YAxis,
	DotProps
} from "recharts";

type MonthlyRecord = {
	monthLabel: string; // e.g. "August"
	period: string; // e.g. "2025-07-07 to 2025-08-06"
	members: number;
	sharedAmount: number; // RM
	hospitalCases: number;
	clinicCases: number;
	avgCommitment: number; // RM
};

const monthNames = [
	"January","February","March","April","May","June","July","August","September","October","November","December"
];

function currency(n: number) { return `RM ${n.toLocaleString("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`; }

export default function RecordsPage() {
	const now = new Date();
	const [year, setYear] = useState<number>(now.getFullYear());
	const [month, setMonth] = useState<number>(now.getMonth());

	// mock data generator for demo; replace with API when available
	const data: MonthlyRecord[] = useMemo(() => {
		const base = 2800;
		return Array.from({ length: 18 }).map((_, i) => {
			const date = new Date(year, month - (i), 1);
			const members = Math.max(600, Math.round(base - i * 80 + (i % 4) * 30));
			const clinicCases = Math.max(0, Math.round(400 - i * 15 + (i % 5) * 8));
			const hospitalCases = Math.max(0, Math.round(7 + (i % 6) - Math.floor(i / 6)));
			const avgCommit = Math.max(2, +(30 + (i % 7) * 1.3 - Math.floor(i / 5) * 5).toFixed(2));
			const total = +(members * avgCommit).toFixed(2);
			return {
				monthLabel: monthNames[date.getMonth()],
				period: `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-07 to ${date.getFullYear()}-${String(date.getMonth()+2).padStart(2,'0')}-06`,
				members,
				sharedAmount: total,
				hospitalCases,
				clinicCases,
				avgCommitment: avgCommit,
			};
		});
	}, [year, month]);

	const current = data[0];
	const totalApproved = current.hospitalCases + current.clinicCases;

	// Prepare chart dataset newest on right
	const chartData = data
		.slice(0, 16)
		.map((d, idx) => ({
			label: d.monthLabel.slice(0, 3),
			value: d.avgCommitment,
		}))
		.reverse();

	return (
		<div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6">
			<div className="w-full max-w-4xl xl:max-w-6xl green-gradient-border p-3 sm:p-4 md:p-6 lg:p-8 xl:p-10">
				<div className="flex flex-col gap-4 sm:gap-5 md:gap-7">
					<div className="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
						<h2 className="text-lg sm:text-xl md:text-2xl lg:text-3xl font-semibold text-gray-800">Sharing Record of {monthNames[month]} {year}</h2>
						<div className="flex gap-2">
							<select value={month} onChange={(e)=>setMonth(parseInt(e.target.value))} className="border border-gray-200 rounded-lg px-2 sm:px-3 py-2 text-sm">
								{monthNames.map((m, idx)=> <option key={m} value={idx}>{m}</option>)}
							</select>
							<select value={year} onChange={(e)=>setYear(parseInt(e.target.value))} className="border border-gray-200 rounded-lg px-2 sm:px-3 py-2 text-sm">
								{Array.from({length:5}).map((_,i)=>{
									const y = now.getFullYear()-i;
									return <option key={y} value={y}>{y}</option>;
								})}
							</select>
						</div>
					</div>

					{/* KPIs */}
					<div className="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 md:gap-4">
						<KPI icon={<DollarSign className="w-4 h-4" />} label="Total shared medical cost" value={currency(current.sharedAmount)} />
						<KPI icon={<Users className="w-4 h-4" />} label="Active members of the month" value={current.members.toLocaleString()} />
						<KPI icon={<CheckCircle2 className="w-4 h-4" />} label="Total hospital cases approved" value={current.hospitalCases.toString()} />
						<KPI icon={<CheckCircle2 className="w-4 h-4" />} label="Total clinic cases approved" value={current.clinicCases.toString()} />
						<KPI icon={<Activity className="w-4 h-4" />} label="Fee Shared Per Member" value={currency(current.avgCommitment)} />
					</div>

					{/* Area Chart */}
					<div>
						<div className="text-xs text-gray-500 mb-2">Average Commitment Statistic (RM)</div>
						<div className="rounded-xl border border-blue-100 bg-white p-2 sm:p-3">
							<div className="w-full h-48 sm:h-56 md:h-64">
								<ResponsiveContainer width="100%" height="100%">
									<AreaChart data={chartData} margin={{ top: 10, right: 10, bottom: 10, left: 0 }}>
										<defs>
											<linearGradient id="colorArea" x1="0" y1="0" x2="0" y2="1">
												<stop offset="0%" stopColor="#38bdf8" stopOpacity={0.6} />
												<stop offset="100%" stopColor="#1e3a8a" stopOpacity={0.15} />
											</linearGradient>
										</defs>
										<CartesianGrid stroke="#e5e7eb" strokeDasharray="3 3" vertical={true} />
										<XAxis dataKey="label" tick={{ fontSize: 12, fill: '#6b7280' }} axisLine={false} tickLine={false} />
										<YAxis tick={{ fontSize: 12, fill: '#6b7280' }} axisLine={false} tickLine={false} width={30} />
										<Tooltip formatter={(v: number | string)=>`RM ${Number(v).toFixed(2)}`} cursor={{ stroke: '#93c5fd', strokeWidth: 1 }} contentStyle={{ borderRadius: 12, borderColor: '#bfdbfe' }} />
										<Area type="monotone" dataKey="value" stroke="#0ea5e9" strokeWidth={2}
											fill="url(#colorArea)"
											activeDot={{ r: 4, fill: '#22c55e', stroke: '#0ea5e9', strokeWidth: 2 }}
											dot={{ r: 3, fill: '#22c55e', stroke: '#0ea5e9', strokeWidth: 1 }}
										/>
									</AreaChart>
								</ResponsiveContainer>
							</div>
						</div>
					</div>

					{/* Table */}
					<div className="overflow-x-auto">
						<table className="min-w-full text-left text-xs sm:text-sm">
							<thead>
								<tr className="text-gray-600">
									<th className="py-2 px-2 sm:px-3">Month</th>
									<th className="py-2 px-2 sm:px-3">Period</th>
									<th className="py-2 px-2 sm:px-3">No. of Member</th>
									<th className="py-2 px-2 sm:px-3">Total Share Amount</th>
									<th className="py-2 px-2 sm:px-3">No. of Hospital Cases</th>
									<th className="py-2 px-2 sm:px-3">No. of Clinic Cases</th>
									<th className="py-2 px-2 sm:px-3">Average Commitment</th>
									<th className="py-2 px-2 sm:px-3">Report</th>
								</tr>
							</thead>
							<tbody className="divide-y">
								{data.map((row, idx) => (
									<tr key={idx} className="hover:bg-blue-50/50">
										<td className="py-2 px-2 sm:px-3">{row.monthLabel}</td>
										<td className="py-2 px-2 sm:px-3 text-gray-600 whitespace-nowrap">{row.period}</td>
										<td className="py-2 px-2 sm:px-3">{row.members.toLocaleString()}</td>
										<td className="py-2 px-2 sm:px-3">{currency(row.sharedAmount)}</td>
										<td className="py-2 px-2 sm:px-3">{row.hospitalCases}</td>
										<td className="py-2 px-2 sm:px-3">{row.clinicCases}</td>
										<td className="py-2 px-2 sm:px-3">{currency(row.avgCommitment)}</td>
										<td className="py-2 px-2 sm:px-3">
											<button className="h-7 sm:h-8 px-2 sm:px-3 rounded-md bg-blue-600 text-white text-xs hover:bg-blue-700 transition">View</button>
										</td>
									</tr>
								))}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	);
}

function KPI({ icon, label, value }: { icon: React.ReactNode; label: string; value: string }) {
	return (
		<div className="rounded-lg sm:rounded-xl border border-blue-100 bg-white p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
			<div className="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-green-100 text-green-700 grid place-content-center">
				{icon}
			</div>
			<div>
				<div className="text-[10px] sm:text-[11px] md:text-xs text-gray-500">{label}</div>
				<div className="text-sm sm:text-base md:text-lg font-semibold text-gray-800">{value}</div>
			</div>
		</div>
	);
}

function ResponsiveArea({ points, values }: { points: string; values: number[] }) {
	return (
		<div className="w-full h-56 sm:h-64">
			<svg viewBox="0 0 100 100" preserveAspectRatio="none" className="w-full h-full">
				<defs>
					<linearGradient id="areaColor" x1="0" x2="0" y1="0" y2="1">
						<stop offset="0%" stopColor="#38bdf8" stopOpacity="0.6" />
						<stop offset="100%" stopColor="#1e3a8a" stopOpacity="0.15" />
					</linearGradient>
					<linearGradient id="lineColor" x1="0" x2="1" y1="0" y2="0">
						<stop offset="0%" stopColor="#22c55e" />
						<stop offset="100%" stopColor="#0ea5e9" />
					</linearGradient>
				</defs>
				<polyline points={points} fill="url(#areaColor)" stroke="none" />
				<polyline points={points} fill="none" stroke="url(#lineColor)" strokeWidth="1.2" />
				{values.map((v, i) => {
					if (i % 4 !== 0) return null;
					const x = (i / (values.length - 1)) * 100;
					return <line key={i} x1={x} x2={x} y1={95} y2={98} stroke="#93c5fd" strokeWidth={0.3} />
				})}
			</svg>
		</div>
	);
}



"use client";

import Image from "next/image";
import { useState } from "react";

export interface MemberProfile {
	name: string;
	nric: string;
	race: string;
	status: string;
	paymentTerms: string;
	packageName: string;
	validity: string;
	relationship: string;
	registeredAt: string;
	emergencyName: string;
	emergencyPhone: string;
	emergencyRelationship: string;
}

export function MemberDetails({ member }: { member: MemberProfile }) {
	const tabs = [
		"Basic",
		"Benefits",
		"Sharing Account Record",
		"Medical Profile",
		"Admission Card",
		"Appreciation Cert",
		"Payment History",
		"Reimbursement Claim",
	];
	const [tab, setTab] = useState(tabs[0]);

	return (
		<div>
			<div className="text-lg font-semibold mb-3">Member Details</div>
			<div className="flex flex-wrap gap-2 border-b pb-2 mb-4">
				{tabs.map((t) => (
					<button key={t} onClick={() => setTab(t)} className={`px-3 py-2 rounded-md text-sm ${t===tab?"bg-emerald-600 text-white":"hover:bg-gray-100"}`}>{t}</button>
				))}
			</div>

			{tab === "Basic" && (
				<div className="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
					<Field label="Member Name" value={member.name} />
					<Field label="NRIC" value={member.nric} />
					<Field label="Race" value={member.race} />
					<Field label="Status" value={member.status} />
					<Field label="Payment Terms" value={member.paymentTerms} />
					<Field label="Package" value={member.packageName} />
					<Field label="Membership Validity" value={member.validity} />
					<Field label="Relationship with user" value={member.relationship} />
					<Field label="Registration Date" value={member.registeredAt} />
					<div className="sm:col-span-2 h-px bg-gray-200 my-2" />
					<Field label="Emergency Contact Name" value={member.emergencyName} />
					<Field label="Emergency Contact Phone Number" value={member.emergencyPhone} />
					<Field label="Emergency Contact Relationship" value={member.emergencyRelationship} />
					<div className="sm:col-span-2 flex gap-2 mt-2">
						<button className="h-9 px-3 rounded-md bg-rose-600 text-white">Withdraw</button>
						<button className="h-9 px-3 rounded-md border">Update Plan</button>
						<button className="h-9 px-3 rounded-md border">Update Member Info</button>
					</div>
				</div>
			)}

			{tab === "Benefits" && (
				<div className="overflow-x-auto">
					<table className="min-w-full text-sm">
						<thead className="bg-gray-100 text-gray-700">
							<tr>
								<th className="px-3 py-2 text-left">Title</th>
								<th className="px-3 py-2 text-left">Description</th>
								<th className="px-3 py-2 text-right">Amount (MYR)</th>
							</tr>
						</thead>
						<tbody className="divide-y">
							{[{
								title: "In-patient Medical Cost", desc: "-", amt: "1,000,000.00"
							},{
								title: "Daily Cash Allowance in Government Hospital", desc: "-", amt: "50.00"
							},{
								title: "Accidental Injury Surgery / Treatment", desc: "Applicable for in-patient treatment and must first be paid by the Member before their case can be submitted for claims during the Waiting Period. Post Waiting Period, the standard in-patient medical cost of RM1,000,000 applies", amt: "10,000.00"
							},{
								title: "Bereavement Allowance", desc: "Upon death of Sharer, RM10,000 will be paid to their appointed beneficiary as listed in the system", amt: "10,000.00"
							},{
								title: "Out-patient Cancer Treatment", desc: "Chemotherapy, Radiotherapy and Electrotherapy are eligible for sharing up to RM100,000, which is excluded from the RM1,000,000 In-Patient Hospitalization Medical Expenses. The Sharer is required to complete the 180-day Waiting Period", amt: "100,000.00"
							}].map((r,i)=> (
								<tr key={i} className="align-top">
									<td className="px-3 py-3 font-medium">{r.title}</td>
									<td className="px-3 py-3 text-gray-700">{r.desc}</td>
									<td className="px-3 py-3 text-right">{r.amt}</td>
								</tr>
							))}
						</tbody>
					</table>
				</div>
			)}

			{tab === "Sharing Account Record" && (
				<div className="overflow-x-auto">
					<table className="min-w-full text-sm">
						<thead className="bg-gray-100 text-gray-700">
							<tr>
								<th className="px-3 py-2 text-left">Date</th>
								<th className="px-3 py-2 text-left">Description</th>
								<th className="px-3 py-2 text-left">Amount (MYR)</th>
								<th className="px-3 py-2 text-left">Balance (MYR)</th>
							</tr>
						</thead>
						<tbody className="divide-y">
							{[
								["2025-08-12 05:00 AM","Payment","50.28","100.00"],
								["2025-08-07 01:37 PM","Share Commitment","-31.72","49.72"],
								["2025-07-07 01:43 PM","Share Commitment","-18.56","81.44"],
								["2025-06-10 08:29 AM","Payment","89.83","100.00"],
							].map((r,i)=> (
								<tr key={i} className={i===2?"bg-gray-50":""}>
									{r.map((c,j)=>(<td key={j} className="px-3 py-3">{c}</td>))}
								</tr>
							))}
						</tbody>
					</table>
				</div>
			)}

			{tab === "Medical Profile" && (
				<div className="space-y-3">
					<Question label="What's your height in cm?" value="168" />
					<Question label="What's your weight in kg?" value="70" />
					<Question label="Within the past 2 years, have you consulted a specialist, been hospitalised, had surgery, had a diagnostic test with an abnormal result or been advised to have any of these in the future?" value="No" />
					<Question label="Have you ever received a diagnosis or shown symptoms of:Cancer or tumors; Heart attack or chest pain; High blood pressure, stroke, or diabetes; Hepatitis B or C; HIV or AIDS; Any mental or nervous disorders; Alcohol or drug abuse; Liver, lung, kidney, bowel, neurological, or musculoskeletal disorders; Any other serious illnesses?" value="No" />
					<Question label="Have you ever had any insurance / takaful application declined?" value="No" />
					<Question label="Have you had any serious injuries (excluding minor cuts, bruises, abrasions, and insect bites) that required hospital admission or a long period of recovery at home?" value="No" />
				</div>
			)}

			{tab === "Admission Card" && (
				<div className="max-w-3xl">
					<Image src="/login-illustration.svg" alt="Admission Card" width={1000} height={560} className="w-full h-auto" />
				</div>
			)}

			{tab === "Appreciation Cert" && (
				<div className="max-w-3xl">
					<Image src="/window.svg" alt="Certificate" width={1000} height={600} className="w-full h-auto" />
				</div>
			)}

			{tab === "Payment History" && (
				<div className="overflow-x-auto">
					<table className="min-w-full text-sm">
						<thead className="bg-gray-100 text-gray-700">
							<tr>
								<th className="px-3 py-2 text-left">Description</th>
								<th className="px-3 py-2 text-left">Amount (MYR)</th>
								<th className="px-3 py-2 text-left">Method</th>
								<th className="px-3 py-2 text-left">Status</th>
								<th className="px-3 py-2 text-left">Date</th>
								<th className="px-3 py-2 text-left">Action</th>
							</tr>
						</thead>
						<tbody className="divide-y">
							{Array.from({length:6}).map((_,i)=> (
								<tr key={i} className={i===1?"bg-gray-50":""}>
									<td className="px-3 py-3">Membership Fee</td>
									<td className="px-3 py-3">40.00</td>
									<td className="px-3 py-3">Mandate</td>
									<td className="px-3 py-3">Success</td>
									<td className="px-3 py-3">2025-08-10 03:00 AM</td>
									<td className="px-3 py-3"><button className="h-8 px-3 rounded-md border">Print Receipt</button></td>
								</tr>
							))}
						</tbody>
					</table>
				</div>
			)}

			{tab === "Reimbursement Claim" && (
				<div className="space-y-4">
					<div className="text-lg font-semibold">Reimbursement Claim Form</div>
					<p className="text-gray-600 text-sm">This form is for requesting reimbursement of expenses you have paid on behalf of the organization or under an eligible policy. Fill in your details, list the expenses with supporting documents, and submit for approval and processing.</p>
					<button className="h-10 px-4 rounded-md bg-emerald-600 text-white">Apply for Reimbursement Claim</button>
					<div className="pt-2">
						<button className="h-9 px-3 rounded-md bg-rose-600 text-white">Withdraw</button>
					</div>
				</div>
			)}
		</div>
	);
}

function Field({ label, value }: { label: string; value: string }) {
	return (
		<div>
			<div className="text-gray-500 text-xs">{label}</div>
			<div className="font-medium">{value}</div>
		</div>
	);
}

function Question({ label, value }: { label: string; value: string }) {
	return (
		<div>
			<div className="text-sm mb-1">{label}</div>
			<div className="h-11 rounded-md bg-gray-100 border border-gray-200 px-3 flex items-center text-gray-700">{value}</div>
		</div>
	);
}



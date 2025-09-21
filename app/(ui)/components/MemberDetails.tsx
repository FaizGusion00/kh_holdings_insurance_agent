"use client";

import Image from "next/image";
import { useState, useEffect } from "react";
import { apiService } from "@/app/services/api";
import { LoadingSpinner } from "./LoadingSpinner";
import { getInsuranceBenefits } from "./InsuranceBenefits";

export interface MemberProfile {
	id?: number;
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
	insurance_plan?: string;
	active_policies_count?: number;
}

export function MemberDetails({ member }: { member: MemberProfile }) {
	const tabs = [
		"Basic",
		"Benefits",
		"Medical Profile",
		"Admission Card",
		"Appreciation Cert",
		"Reimbursement Claim",
	];
	const [tab, setTab] = useState(tabs[0]);
	const [memberDetails, setMemberDetails] = useState<any>(null);
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState<string | null>(null);

	// Fetch member details
	useEffect(() => {
		const fetchMemberData = async () => {
			if (!member.id) return;
			
			setLoading(true);
			setError(null);
			
			try {
				// Get member details
				const detailsResponse = await apiService.getMemberDetails(member.id);
				if (detailsResponse.success && detailsResponse.data) {
					setMemberDetails(detailsResponse.data);
				}
			} catch (error) {
				console.error('Error fetching member data:', error);
				setError('Failed to load member data');
			} finally {
				setLoading(false);
			}
		};
		
		fetchMemberData();
	}, [member.id]);


	if (loading) {
		return (
			<div className="flex items-center justify-center py-8">
				<LoadingSpinner size="lg" />
				<span className="ml-2">Loading member details...</span>
			</div>
		);
	}

	return (
		<div>
			<div className="text-lg font-semibold mb-3">Member Details</div>
			{error && (
				<div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
					<div className="text-red-600">{error}</div>
				</div>
			)}
			<div className="flex flex-wrap gap-2 border-b pb-2 mb-4">
				{tabs.map((t) => (
					<button key={t} onClick={() => setTab(t)} className={`px-3 py-2 rounded-md text-sm ${t===tab?"bg-emerald-600 text-white":"hover:bg-gray-100"}`}>{t}</button>
				))}
			</div>

			{tab === "Basic" && (
				<div className="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
					<Field label="Member Name" value={memberDetails?.name || member.name} />
					<Field label="NRIC" value={memberDetails?.nric || member.nric || "Not specified"} />
					<Field label="Race" value={memberDetails?.race || member.race || "Not specified"} />
					<Field label="Status" value={memberDetails?.status || member.status} />
					<Field label="Payment Terms" value={member.paymentTerms} />
					<Field label="Package" value={
						(member.active_policies_count && member.active_policies_count > 0) 
							? (memberDetails?.insurance_plan || member.insurance_plan || "Active Plan")
							: "No Plan Purchased"
					} />
					<Field label="Membership Validity" value={member.validity} />
					<Field label="Relationship with user" value={memberDetails?.relationship_with_agent || member.relationship} />
					<Field label="Registration Date" value={memberDetails?.registration_date || member.registeredAt} />
					<div className="sm:col-span-2 h-px bg-gray-200 my-2" />
					<Field label="Emergency Contact Name" value={memberDetails?.emergency_contact_name || member.emergencyName || "Not specified"} />
					<Field label="Emergency Contact Phone Number" value={memberDetails?.emergency_contact_phone || member.emergencyPhone || "Not specified"} />
					<Field label="Emergency Contact Relationship" value={memberDetails?.emergency_contact_relationship || member.emergencyRelationship || "Not specified"} />
				</div>
			)}

			{tab === "Benefits" && (
				<div className="overflow-x-auto">
					{(member.active_policies_count && member.active_policies_count > 0) ? (
						<>
							<div className="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
								<h3 className="font-semibold text-blue-800 mb-2">
									Insurance Plan: {memberDetails?.insurance_plan || member.insurance_plan || 'MediPlan Coop'}
								</h3>
								<p className="text-blue-600 text-sm">
									Benefits and coverage details for this member's insurance plan
								</p>
							</div>
							<table className="min-w-full text-sm">
								<thead className="bg-gray-100 text-gray-700">
									<tr>
										<th className="px-3 py-2 text-left">Benefit</th>
										<th className="px-3 py-2 text-left">Description</th>
										<th className="px-3 py-2 text-right">Amount (RM)</th>
									</tr>
								</thead>
								<tbody className="divide-y">
									{getInsuranceBenefits(memberDetails?.insurance_plan || member.insurance_plan || 'MediPlan Coop').map((benefit, i) => (
										<tr key={i} className="align-top">
											<td className="px-3 py-3 font-medium">{benefit.title}</td>
											<td className="px-3 py-3 text-gray-700">{benefit.desc}</td>
											<td className="px-3 py-3 text-right font-medium">{benefit.amt}</td>
										</tr>
									))}
								</tbody>
							</table>
						</>
					) : (
						<div className="text-center py-12">
							<div className="text-gray-500 text-lg mb-2">No Insurance Plan</div>
							<div className="text-gray-400 text-sm">This member has not purchased any insurance plan yet.</div>
						</div>
					)}
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
				<div className="max-w-4xl mx-auto">
					{(member.active_policies_count && member.active_policies_count > 0) ? (
						<div className="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 rounded-2xl shadow-2xl p-8 text-white">
							<div className="flex justify-between items-start mb-6">
								<div>
									<h2 className="text-2xl font-bold mb-2">KH Holdings Insurance</h2>
									<p className="text-blue-100">Medical Admission Card</p>
								</div>
								<div className="text-right">
									<div className="bg-white/20 rounded-lg p-3">
										<div className="text-sm text-blue-100">Card ID</div>
										<div className="font-mono font-bold">MC-{member.id || '001'}</div>
									</div>
								</div>
							</div>
							
							<div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
								<div>
									<div className="text-blue-100 text-sm mb-1">Member Name</div>
									<div className="font-semibold text-lg">{memberDetails?.name || member.name}</div>
								</div>
								<div>
									<div className="text-blue-100 text-sm mb-1">NRIC</div>
									<div className="font-mono">{memberDetails?.nric || member.nric || "Not specified"}</div>
								</div>
								<div>
									<div className="text-blue-100 text-sm mb-1">Plan</div>
									<div className="font-semibold">{memberDetails?.insurance_plan || member.insurance_plan || 'MediPlan Coop'}</div>
								</div>
								<div>
									<div className="text-blue-100 text-sm mb-1">Valid Until</div>
									<div className="font-semibold">{new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toLocaleDateString()}</div>
								</div>
							</div>
							
							<div className="border-t border-blue-400/30 pt-4">
								<div className="text-xs text-blue-100 mb-2">Emergency Contact</div>
								<div className="text-sm">
									{memberDetails?.emergency_contact_name || member.emergencyName || 'Not specified'} - 
									{memberDetails?.emergency_contact_phone || member.emergencyPhone || 'Not specified'}
								</div>
							</div>
							
							<div className="mt-6 text-center">
								<div className="inline-block bg-white/10 rounded-lg px-4 py-2">
									<div className="text-xs text-blue-100">24/7 Hotline</div>
									<div className="font-bold">1-800-KH-CARE</div>
								</div>
							</div>
						</div>
					) : (
						<div className="text-center py-12">
							<div className="text-gray-500 text-lg mb-2">No Medical Card Available</div>
							<div className="text-gray-400 text-sm">This member needs to purchase an insurance plan to receive a medical admission card.</div>
						</div>
					)}
				</div>
			)}

			{tab === "Appreciation Cert" && (
				<div className="max-w-4xl mx-auto">
					<div className="bg-gradient-to-br from-amber-50 to-orange-50 border-8 border-double border-amber-400 rounded-3xl shadow-2xl p-12">
						<div className="text-center mb-8">
							<div className="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center">
								<svg className="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
									<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
								</svg>
							</div>
							<h1 className="text-4xl font-bold text-amber-800 mb-2">Certificate of Appreciation</h1>
							<div className="w-32 h-1 bg-gradient-to-r from-amber-400 to-orange-500 mx-auto rounded-full"></div>
						</div>
						
						<div className="text-center mb-8">
							<p className="text-lg text-amber-700 mb-6">
								This certificate is proudly presented to
							</p>
							<h2 className="text-3xl font-bold text-amber-900 mb-6 border-b-2 border-amber-300 pb-2 inline-block">
								{memberDetails?.name || member.name}
							</h2>
							<p className="text-lg text-amber-700 leading-relaxed max-w-2xl mx-auto">
								In recognition of your trust and commitment as a valued member of KH Holdings Insurance. 
								Your participation in our {memberDetails?.insurance_plan || member.insurance_plan || 'MediPlan Coop'} 
								demonstrates your dedication to securing your health and well-being.
							</p>
						</div>
						
						<div className="flex justify-between items-end mt-12">
							<div className="text-center">
								<div className="w-32 h-0.5 bg-amber-400 mb-2"></div>
								<div className="text-sm text-amber-600">Date of Issue</div>
								<div className="font-semibold text-amber-800">{new Date().toLocaleDateString()}</div>
							</div>
							<div className="text-center">
								<div className="w-32 h-0.5 bg-amber-400 mb-2"></div>
								<div className="text-sm text-amber-600">Authorized Signature</div>
								<div className="font-semibold text-amber-800">KH Holdings Insurance</div>
							</div>
						</div>
					</div>
				</div>
			)}


			{tab === "Reimbursement Claim" && (
				<div className="space-y-4">
					<div className="text-lg font-semibold">Reimbursement Claim Form</div>
					<p className="text-gray-600 text-sm">This form is for requesting reimbursement of expenses you have paid on behalf of the organization or under an eligible policy. Fill in your details, list the expenses with supporting documents, and submit for approval and processing.</p>
					<button className="h-10 px-4 rounded-md bg-emerald-600 text-white">Apply for Reimbursement Claim</button>
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



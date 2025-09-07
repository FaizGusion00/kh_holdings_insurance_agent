"use client";

import {
	CircleHelp,
	Plus,
	TrendingUp,
	Users,
	Hospital,
	Activity,
	ArrowUpRight,
	Calendar,
	CreditCard
} from "lucide-react";
import { PageTransition, StaggeredContainer, StaggeredItem, FadeIn } from "../(ui)/components/PageTransition";
import { Modal } from "../(ui)/components/Modal";
import { AddMemberForm } from "../(ui)/components/AddMemberForm";
import { MemberDetails, MemberProfile } from "../(ui)/components/MemberDetails";
import { useState, useEffect } from "react";
import { useAuth } from "../contexts/AuthContext";
import { apiService, Member, DashboardStats } from "../services/api";

// Family Icon using the PNG image
function FamilyIcon({ className }: { className?: string }) {
	return (
		<img 
			src="/assets/add_member_logo.png" 
			alt="Family" 
			className={className}
		/>
	);
}

type MemberDisplay = { name: string; nric: string; balance: number; status: string; initial: string; color: string };

function StatCard({ title, value, highlight, icon: Icon, trend, onAddMember }: { 
	title: string; 
	value: string; 
	highlight?: boolean; 
	icon?: React.ComponentType<{ className?: string }>;
	trend?: { value: string; isPositive: boolean };
	onAddMember?: () => void;
}) {
	return (
		<div className={`rounded-lg sm:rounded-xl md:rounded-2xl p-2.5 sm:p-3 md:p-4 lg:p-5 transition-all duration-300 hover:shadow-lg ${
			highlight 
				? "bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200" 
				: "bg-gradient-to-br from-gray-50 to-white border border-gray-100"
		}`}>
			<div className="flex items-start justify-between">
				<div className="flex-1">
					<div className="flex items-center gap-2 mb-1 sm:mb-2">
						{Icon && <Icon className="w-3 h-3 sm:w-4 sm:h-4 text-[#264EE4] opacity-70" />}
						<div className="text-gray-600 font-medium text-xs sm:text-sm">{title}</div>
					</div>
					<div className={`text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl font-bold ${
						highlight ? "text-[#264EE4]" : "text-gray-800"
					}`}>
						{value}
					</div>
					{trend && (
						<div className="flex items-center gap-1 mt-1 sm:mt-2">
							<ArrowUpRight className={`w-2 h-2 sm:w-3 sm:h-3 ${trend.isPositive ? 'text-[#264EE4]' : 'text-red-500'}`} />
							<span className={`text-xs font-medium ${trend.isPositive ? 'text-[#264EE4]' : 'text-red-600'}`}>
								{trend.value}
							</span>
						</div>
					)}
					{/* Add Member Button */}
					{title === "Total Member" && onAddMember && (
						<button
							onClick={onAddMember}
							className="mt-2 sm:mt-3 px-3 sm:px-4 py-1.5 sm:py-2 bg-gradient-to-r from-teal-400 to-teal-500 hover:from-teal-500 hover:to-teal-600 text-white text-xs sm:text-sm font-semibold rounded-lg transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5 flex items-center gap-2"
						>
							<Plus className="w-3 h-3 sm:w-4 sm:h-4" />
							Add Member
						</button>
					)}
				</div>
				{title === "Total Member" && (
					<div className="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 lg:w-28 lg:h-28 rounded-full bg-blue-50 flex items-center justify-center overflow-hidden">
						<FamilyIcon className="w-full h-full object-cover" />
					</div>
				)}
			</div>
		</div>
	);
}

function MemberCard({ m }: { m: Member }) {
	// Generate initial and color for display
	const initial = m.name.charAt(0);
	const colors = [
		"bg-gradient-to-br from-blue-600 to-blue-700",
		"bg-gradient-to-br from-amber-500 to-amber-600",
		"bg-gradient-to-br from-green-600 to-green-700",
		"bg-gradient-to-br from-purple-600 to-purple-700",
		"bg-gradient-to-br from-red-600 to-red-700"
	];
	const color = colors[m.id % colors.length];
	
	return (
		<div className="rounded-lg sm:rounded-xl border border-gray-100 bg-white p-2.5 sm:p-3 md:p-4 lg:p-5 transition-all duration-300 hover:shadow-md hover:border-blue-200 group">
			<div className="flex items-start gap-2 sm:gap-3 md:gap-4">
				<div className={`w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 lg:w-14 lg:h-14 rounded-full grid place-content-center text-white font-bold text-xs sm:text-sm md:text-base lg:text-lg flex-shrink-0 shadow-lg ${color}`}>
					{initial}
				</div>
				<div className="flex-1 space-y-1.5 sm:space-y-2 min-w-0">
					<div className="space-y-1">
						<div className="text-xs sm:text-sm text-gray-500 font-medium">Name</div>
						<div className="text-xs sm:text-sm md:text-base font-semibold text-gray-800 truncate">{m.name}</div>
					</div>
					<div className="grid grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm">
						<div>
							<div className="text-gray-500">NRIC</div>
							<div className="font-medium text-gray-700">{m.nric}</div>
						</div>
						<div>
							<div className="text-gray-500">Balance</div>
							<div className="font-semibold text-emerald-600">RM {m.balance.toFixed(2)}</div>
						</div>
					</div>
				</div>
				<div className="flex flex-col items-end gap-1.5 sm:gap-2">
					<span className={`px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] md:text-xs font-semibold leading-none ${
						m.status === "Active" 
							? "bg-green-100 text-green-700 border border-green-200" 
							: "bg-gray-100 text-gray-700 border border-gray-200"
					}`}>
						{m.status}
					</span>
					<div className="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
						<ArrowUpRight className="w-3 h-3 sm:w-4 sm:h-4 text-[#264EE4]" />
					</div>
				</div>
			</div>
		</div>
	);
}

export default function DashboardPage() {
	const { user } = useAuth();
	const [showAdd, setShowAdd] = useState(false);
	const [showDetails, setShowDetails] = useState<null | MemberProfile>(null);
	const [currentDateTime, setCurrentDateTime] = useState<Date | null>(null);
	const [isMounted, setIsMounted] = useState(false);
	const [dashboardData, setDashboardData] = useState<DashboardStats | null>(null);
	const [members, setMembers] = useState<Member[]>([]);
	const [isLoading, setIsLoading] = useState(true);
	
	// Fetch dashboard data
	useEffect(() => {
		const fetchDashboardData = async () => {
			try {
				const [statsResponse, membersResponse] = await Promise.all([
					apiService.getDashboardStats(),
					apiService.getMembers()
				]);
				
				if (statsResponse.success && statsResponse.data) {
					setDashboardData(statsResponse.data.stats);
				}
				
				if (membersResponse.success && membersResponse.data) {
					setMembers(membersResponse.data.data);
				}
			} catch (error) {
				console.error('Failed to fetch dashboard data:', error);
			} finally {
				setIsLoading(false);
			}
		};

		fetchDashboardData();
	}, []);
	
	// Update date and time every second (client-only) to avoid hydration mismatch
	useEffect(() => {
		setIsMounted(true);
		setCurrentDateTime(new Date());
		const timer = setInterval(() => {
			setCurrentDateTime(new Date());
		}, 1000);

		return () => clearInterval(timer);
	}, []);
	
	return (
		<PageTransition>
					<div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6 bg-gradient-to-br from-blue-50/30 via-white to-blue-50/30">
			<div className="relative w-full max-w-5xl xl:max-w-6xl blue-gradient-border p-1.5 sm:p-2 md:p-3 lg:p-4 xl:p-6">
					{/* Profile Badge */}
					<FadeIn delay={0.3}>
						<div className="absolute right-2 sm:right-3 md:right-6 top-2 sm:top-3 md:top-6">
							<div className="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-rose-400 to-rose-500 text-white grid place-content-center font-bold text-sm sm:text-base shadow-lg">
								N
							</div>
						</div>
					</FadeIn>
					
					<div className="flex flex-col gap-2 sm:gap-3 md:gap-4 lg:gap-5">
						{/* Header Section */}
						<FadeIn delay={0.4}>
							<div className="text-center sm:text-left">
								{/* Today Row */}
								<div className="flex items-center justify-center sm:justify-start gap-2 mb-1.5 sm:mb-2">
									<div className="flex items-center gap-2">
										<div className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full bg-blue-100 flex items-center justify-center">
											<Calendar className="w-3 h-3 sm:w-4 sm:h-4 text-[#264EE4]" />
										</div>
										<span className="text-[#264EE4] text-xs sm:text-sm font-semibold">Today</span>
									</div>
								</div>
								
								{/* Date and Time Row */}
								<div className="flex items-center justify-center sm:justify-start gap-2 sm:gap-3 md:gap-4 mb-2 sm:mb-3">
									<span className="text-gray-600 text-xs sm:text-sm font-medium" suppressHydrationWarning>
										{isMounted && currentDateTime ? currentDateTime.toLocaleDateString('en-US', { 
											weekday: 'long', 
											year: 'numeric', 
											month: 'long', 
											day: 'numeric' 
										}) : ''}
									</span>
									<span className="text-gray-500 text-xs sm:text-sm" suppressHydrationWarning>
										{isMounted && currentDateTime ? currentDateTime.toLocaleTimeString('en-US', { 
											hour: '2-digit', 
											minute: '2-digit', 
											second: '2-digit',
											hour12: true 
										}) : ''}
									</span>
								</div>
								
								{/* Divider Line */}
								<div className="flex justify-center sm:justify-start mb-3 sm:mb-4">
									<div className="w-16 sm:w-20 h-0.5 bg-gradient-to-r from-[#264EE4] via-[#264EE4] to-[#264EE4] rounded-full"></div>
								</div>
								
								{/* Welcome Message */}
								<div className="mb-2 sm:mb-3 md:mb-4">
									<p className="text-gray-500 text-xs sm:text-sm mb-1.5 sm:mb-2 md:mb-3">Hello, welcome back!</p>
									<h1 className="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl font-bold leading-tight bg-gradient-to-r from-gray-800 via-[#264EE4] to-[#264EE4] bg-clip-text text-transparent">
										{user?.name || 'Loading...'}
									</h1>
								</div>
							</div>
						</FadeIn>
						
						<div className="grid grid-cols-1 lg:grid-cols-[1fr_1fr] xl:grid-cols-[360px_1fr] gap-2 sm:gap-3 md:gap-4 lg:gap-5">
							{/* Left Column - Enhanced Stats */}
							<StaggeredContainer className="grid grid-cols-1 gap-1.5 sm:gap-2 md:gap-3 lg:gap-4">
								<StaggeredItem>
									<StatCard 
										title="Total Member" 
										value={dashboardData ? dashboardData.total_members.toString() : '0'} 
										highlight 
										icon={Users}
										trend={{ value: `+${dashboardData?.new_members || 0} this month`, isPositive: true }}
										onAddMember={() => setShowAdd(true)}
									/>
								</StaggeredItem>
								<StaggeredItem>
									<StatCard 
										title="Commission Earned" 
										value={`RM ${dashboardData ? parseFloat(dashboardData.total_commission_earned).toLocaleString() : '0.00'}`} 
										icon={TrendingUp}
										trend={{ value: `${dashboardData?.target_achievement || 0}% of target`, isPositive: dashboardData ? dashboardData.target_achievement >= 50 : false }}
									/>
								</StaggeredItem>
								<StaggeredItem>
									<StatCard 
										title="Active Members" 
										value={dashboardData ? dashboardData.active_members.toString() : '0'} 
										icon={Hospital}
									/>
								</StaggeredItem>
								<StaggeredItem>
									<StatCard 
										title="Network Level" 
										value={`Level ${dashboardData ? dashboardData.mlm_level : 0}`} 
										icon={Activity}
									/>
								</StaggeredItem>
							</StaggeredContainer>
							
							{/* Right Column - Enhanced Member List */}
							<FadeIn delay={0.6}>
								<div className="flex flex-col gap-1.5 sm:gap-2 md:gap-3 lg:gap-4">
									<div className="flex items-center justify-between">
										<div>
											<div className="flex items-center gap-2 mb-1 sm:mb-2">
												<Users className="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5 text-[#264EE4]" />
												<div className="font-bold text-xs sm:text-sm md:text-base text-gray-800">My Member List</div>
											</div>
											<div className="h-0.5 sm:h-1 w-10 sm:w-12 md:w-16 bg-gradient-to-r from-[#264EE4] to-[#264EE4] rounded-full" />
										</div>
										<button 
											onClick={()=> setShowAdd(true)} 
											className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 lg:w-9 lg:h-9 rounded-full bg-gradient-to-r from-[#264EE4] to-[#264EE4] text-white grid place-content-center hover:from-[#264EE4]/90 hover:to-[#264EE4]/90 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
										>
											<Plus size={12} className="sm:w-[14px] sm:h-[14px] md:w-[16px] md:h-[16px]" />
										</button>
									</div>
									<StaggeredContainer className="grid gap-1.5 sm:gap-2 md:gap-3 lg:gap-4">
										{isLoading ? (
											<div className="text-center py-8 text-gray-500">Loading members...</div>
										) : members.length > 0 ? (
											members.map((m) => (
												<StaggeredItem key={m.id}>
													<button 
														onClick={()=> setShowDetails({
															name: m.name,
															nric: m.nric,
															race: m.race || "Not specified",
															status: m.status,
															paymentTerms: "Monthly ( RM40 per month )",
															packageName: "Standard",
															validity: "13 Days",
															relationship: m.relationship_with_agent || "Not specified",
															registeredAt: m.registration_date,
															emergencyName: m.emergency_contact_name || "Not specified",
															emergencyPhone: m.emergency_contact_phone || "Not specified",
															emergencyRelationship: m.emergency_contact_relationship || "Not specified",
														})} 
														className="block text-left w-full"
													>
														<MemberCard m={m} />
													</button>
												</StaggeredItem>
											))
										) : (
											<div className="text-center py-8 text-gray-500">No members found</div>
										)}
									</StaggeredContainer>
								</div>
							</FadeIn>
						</div>
					</div>
				</div>
			</div>

			{/* Add Member Modal */}
			<Modal open={showAdd} onClose={()=> setShowAdd(false)} title="Add Member" maxWidth="max-w-4xl">
				<AddMemberForm onSubmit={async (data) => {
					try {
						const response = await apiService.createMember({
							name: data.name,
							nric: data.nric,
							phone: data.phone,
							email: data.email,
							address: data.address,
							date_of_birth: data.date_of_birth,
							gender: data.gender,
							occupation: data.occupation,
							race: data.race,
							relationship_with_agent: data.relationship_with_agent,
							emergency_contact_name: data.emergency_contact_name,
							emergency_contact_phone: data.emergency_contact_phone,
							emergency_contact_relationship: data.emergency_contact_relationship,
						});
						
						if (response.success) {
							// Refresh the members list
							const membersResponse = await apiService.getMembers();
							if (membersResponse.success && membersResponse.data) {
								setMembers(membersResponse.data.data);
							}
							setShowAdd(false);
						} else {
							alert('Failed to create member: ' + response.message);
						}
					} catch (error) {
						console.error('Error creating member:', error);
						alert('Failed to create member. Please try again.');
					}
				}} />
			</Modal>

			{/* Member Details Modal */}
			<Modal open={!!showDetails} onClose={()=> setShowDetails(null)} maxWidth="max-w-5xl">
				{showDetails && <MemberDetails member={showDetails} />}
			</Modal>
		</PageTransition>
	);
}



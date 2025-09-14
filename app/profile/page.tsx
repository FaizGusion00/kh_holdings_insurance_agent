"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "../contexts/AuthContext";
import { apiService, PaymentTransaction, PaymentMandate, DashboardStats, RecentActivity } from "../services/api";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
} from 'chart.js';
import { Line } from 'react-chartjs-2';

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
);

import { 
    LogOut, 
    Eye, 
    User as UserIcon, 
    DollarSign, 
    FileText, 
    Crown, 
    ChevronDown,
    CheckCircle,
    Printer,
    Download,
    X,
    Search,
    Share2,
    Grid3X3,
    ArrowLeft,
    Shield,
    Phone,
    Plus,
    XCircle,
    Wallet,
    Target,
    Users,
    Activity,
    Clock,
    BarChart3
} from "lucide-react";
import { PageTransition, FadeIn, StaggeredContainer, StaggeredItem } from "../(ui)/components/PageTransition";
import { motion, AnimatePresence } from "framer-motion";
import MedicalInsuranceRegistrationForm from "../(ui)/components/MedicalInsuranceRegistrationForm";

type TabType = 'overview' | 'profile' | 'policy-view' | 'payment-history' | 'medical-insurance' | 'referrer';
type ReferrerSubTab = 'referral' | 'bank-info' | 'commission';

interface PaymentData {
    description: string;
    amount: string;
    method: string;
    status: string;
    date: string;
}

export default function ProfilePage() {
	const router = useRouter();
    const { user, logout: authLogout, updateUser } = useAuth();
    const [activeTab, setActiveTab] = useState<TabType>('overview');
    const [activeReferrerTab, setActiveReferrerTab] = useState<ReferrerSubTab>('referral');
    const [showReceiptModal, setShowReceiptModal] = useState(false);
    const [selectedPayment, setSelectedPayment] = useState<PaymentData | null>(null);

    // New modal states
    const [showUpdateProfileModal, setShowUpdateProfileModal] = useState(false);
    const [showChangePhoneModal, setShowChangePhoneModal] = useState(false);
    const [showChangePasswordModal, setShowChangePasswordModal] = useState(false);
    const [showLogoutConfirmModal, setShowLogoutConfirmModal] = useState(false);
    const [showMedicalInsuranceModal, setShowMedicalInsuranceModal] = useState(false);
    const [showPolicyModal, setShowPolicyModal] = useState(false);
    const [selectedPolicy, setSelectedPolicy] = useState<any>(null);
    
    // Medical Insurance search and pagination states
    const [searchQuery, setSearchQuery] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [itemsPerPage] = useState(5);
    const [phoneChangeStep, setPhoneChangeStep] = useState<'initial' | 'tac-verification' | 'new-phone' | 'new-tac-verification' | 'success'>('initial');
    const [tacCode, setTacCode] = useState('');
    const [newPhoneNumber, setNewPhoneNumber] = useState('');
    const [newTacCode, setNewTacCode] = useState('');
    const [oldPassword, setOldPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [passwordError, setPasswordError] = useState('');

    // API data states
    const [paymentHistory, setPaymentHistory] = useState<PaymentTransaction[]>([]);
    const [gatewayPayments, setGatewayPayments] = useState<any[]>([]);
    const [medicalInsurancePolicies, setMedicalInsurancePolicies] = useState<any[]>([]);
    const [mandates, setMandates] = useState<PaymentMandate[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');
    const [clients, setClients] = useState<any[]>([]);
    const [selectedClient, setSelectedClient] = useState<any | null>(null);
    const [clientPayments, setClientPayments] = useState<any[]>([]);
    const [showClientModal, setShowClientModal] = useState(false);
    
    // Enhanced overview states
    const [dashboardStats, setDashboardStats] = useState<DashboardStats | null>(null);
    const [recentActivities, setRecentActivities] = useState<RecentActivity[]>([]);
    const [walletData, setWalletData] = useState<any>(null);
    const [commissionHistory, setCommissionHistory] = useState<any[]>([]);
    const [isOverviewLoading, setIsOverviewLoading] = useState(false);

    // Update profile form when user data changes
    useEffect(() => {
        if (user) {
            setProfileForm({
                name: user.name || "",
                email: user.email || "",
                streetAddress: user.address || "",
                postalCode: user.postal_code || "",
                state: user.state || "",
                city: user.city || "",
                accountPassword: ""
            });
        }
    }, [user]);

    // Load overview data
    const loadOverviewData = async () => {
        setIsOverviewLoading(true);
        try {
            const [dashboardResponse, walletResponse, commissionResponse] = await Promise.all([
                apiService.getDashboardStats(),
                apiService.getAgentWallet(),
                apiService.getWalletTransactions(1)
            ]);
            
            if (dashboardResponse.success && dashboardResponse.data) {
                setDashboardStats(dashboardResponse.data.stats);
                setRecentActivities(dashboardResponse.data.recent_activities || []);
            }
            
            if (walletResponse.success && walletResponse.data) {
                setWalletData(walletResponse.data);
            }
            
            if (commissionResponse.success && commissionResponse.data) {
                setCommissionHistory(commissionResponse.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load overview data:', error);
        } finally {
            setIsOverviewLoading(false);
        }
    };

    // Load payments and mandates on enter relevant tabs
    useEffect(() => {
        async function loadData() {
            setError("");
            if (!user) return;
            if (activeTab === 'overview') {
                loadOverviewData();
            } else if (activeTab === 'payment-history') {
                setIsLoading(true);
                try {
                    const res = await apiService.getPaymentHistory(1);
                    if (res.success && res.data) {
                        setPaymentHistory(res.data.data || []);
                    } else {
                        setError(res.message || 'Failed to load payment history');
                    }
                } catch (e) {
                    setError(e instanceof Error ? e.message : 'Failed to load payment history');
                } finally {
                    setIsLoading(false);
                }
                // Load gateway payments in parallel (non-blocking UI)
                try {
                    const gp = await apiService.getGatewayPaymentHistory(1);
                    if (gp.success && gp.data) {
                        setGatewayPayments(gp.data.data || []);
            }
                } catch {}
            }
            if (activeTab === 'medical-insurance') {
                setIsLoading(true);
                try {
                    const res = await apiService.getClients(1);
                    if (res.success && res.data) {
                        setClients(res.data.data || []);
                    } else {
                        setError(res.message || 'Failed to load clients');
                    }
                } catch (e) {
                    setError(e instanceof Error ? e.message : 'Failed to load clients');
                } finally {
                    setIsLoading(false);
                }
            }
        }
        loadData();
    }, [activeTab, user]);

    // Profile form data
    const [profileForm, setProfileForm] = useState({
        name: user?.name || "",
        email: user?.email || "",
        streetAddress: user?.address || "",
        postalCode: user?.postal_code || "",
        state: user?.state || "",
        city: user?.city || "",
        accountPassword: ""
    });

    const handleLogout = async () => {
        try {
            await authLogout();
            router.push("/login");
        } catch (error) {
            console.error('Logout error:', error);
			router.push("/login");
        }
    };

    const handlePrintReceipt = (payment: PaymentData) => {
        setSelectedPayment(payment);
        setShowReceiptModal(true);
    };

    // New modal handlers
    const handleUpdateProfile = () => {
        setShowUpdateProfileModal(true);
    };

    const handleChangePhone = () => {
        setShowChangePhoneModal(true);
        setPhoneChangeStep('initial');
    };

    const handleChangePassword = () => {
        setShowChangePasswordModal(true);
        setPasswordError('');
        setOldPassword('');
        setNewPassword('');
        setConfirmPassword('');
    };

    const handleMedicalInsuranceRegistration = () => {
        setShowMedicalInsuranceModal(true);
    };

    const handleViewPolicy = (policy: any) => {
        setSelectedPolicy(policy);
        setShowPolicyModal(true);
    };

    // Medical Insurance search and pagination logic
    const filteredClients = clients.filter(client => 
        client.full_name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
        client.nric?.toLowerCase().includes(searchQuery.toLowerCase()) ||
        client.phone_number?.toLowerCase().includes(searchQuery.toLowerCase()) ||
        client.email?.toLowerCase().includes(searchQuery.toLowerCase()) ||
        client.plan_name?.toLowerCase().includes(searchQuery.toLowerCase())
    );

    const totalPages = Math.ceil(filteredClients.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedClients = filteredClients.slice(startIndex, endIndex);

    const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSearchQuery(e.target.value);
        setCurrentPage(1); // Reset to first page when searching
    };

    const handlePageChange = (page: number) => {
        setCurrentPage(page);
    };

    const handleMedicalInsuranceSuccess = async (result: any) => {
        // Show confirmation and switch to payment-history tab, pulling new data
        try {
            setShowMedicalInsuranceModal(false);
            setActiveTab('payment-history');
            const res = await apiService.getGatewayPaymentHistory(1);
            if (res.success) {
                // Reuse paymentHistory list visually by mapping gateway entries into a minimal shape
                const rows = (res.data?.data || []).map((g: any) => ({
                    description: g.description || 'Medical Insurance Payment',
                    amount: `RM ${Number(g.amount).toFixed(2)}`,
                    status: g.status,
                    date: new Date(g.completed_at || g.created_at).toLocaleString(),
                    method: 'Card',
                    reference: g.payment_id,
                }));
                // Also refresh standard payment history in background
                try {
                    const ph = await apiService.getPaymentHistory(1);
                    if (ph.success && ph.data) setPaymentHistory(ph.data.data || []);
                } catch {}
                // Fire a simple info toast replacement
                alert('Payment successful. Showing your latest payment history.');
            }
        } catch (e) {
            console.error(e);
        }
    };

    const handlePhoneContinue = async () => {
        try {
            if (phoneChangeStep === 'initial') {
                if (!user?.phone_number) return;
                await apiService.sendTac(user.phone_number, 'change_phone');
                setPhoneChangeStep('tac-verification');
                return;
            }
            if (phoneChangeStep === 'tac-verification') {
                if (!user?.phone_number || !tacCode.trim()) return;
                await apiService.verifyTac(user.phone_number, tacCode.trim(), 'change_phone');
                setPhoneChangeStep('new-phone');
                return;
            }
            if (phoneChangeStep === 'new-phone') {
                if (!newPhoneNumber.trim()) return;
                await apiService.sendTac(newPhoneNumber.trim(), 'verify_new_phone');
                setPhoneChangeStep('new-tac-verification');
                return;
            }
            if (phoneChangeStep === 'new-tac-verification') {
                if (!newPhoneNumber.trim() || !newTacCode.trim() || !user?.phone_number) return;
                // Finalize change with backend
                await apiService.changePhone({
                    current_phone: user.phone_number,
                    new_phone: newPhoneNumber.trim(),
                    tac_code: newTacCode.trim()
                });
                setPhoneChangeStep('success');
                return;
            }
        } catch (e) {
            console.error('Phone change flow error', e);
        }
    };

    const handlePhoneBack = () => {
        if (phoneChangeStep === 'tac-verification') {
            setPhoneChangeStep('initial');
        } else if (phoneChangeStep === 'new-phone') {
            setPhoneChangeStep('tac-verification');
        } else if (phoneChangeStep === 'new-tac-verification') {
            setPhoneChangeStep('new-phone');
        }
    };

    const handlePasswordUpdate = async () => {
        if (!oldPassword || !newPassword || !confirmPassword) {
            setPasswordError('Please fill out this field.');
            return;
        }
        if (newPassword !== confirmPassword) {
            setPasswordError('New passwords do not match.');
            return;
        }
        if (newPassword.length < 6) {
            setPasswordError('Password must be at least 6 characters.');
            return;
        }
        try {
            await apiService.changePassword({
                current_password: oldPassword,
                new_password: newPassword,
                new_password_confirmation: confirmPassword,
            });
            setShowChangePasswordModal(false);
            setPasswordError('');
        } catch (e) {
            setPasswordError(e instanceof Error ? e.message : 'Failed to change password');
        }
    };

    const resetPhoneChange = () => {
        setPhoneChangeStep('initial');
        setTacCode('');
        setNewPhoneNumber('');
        setNewTacCode('');
    };

    const handleSubmitUpdateProfile = async () => {
        try {
            const res = await apiService.updateProfile({
                name: profileForm.name,
                address: profileForm.streetAddress,
                city: profileForm.city,
                state: profileForm.state,
                postal_code: profileForm.postalCode,
            });
            if (res.success && res.data) {
                updateUser(res.data);
                setShowUpdateProfileModal(false);
            }
        } catch (e) {
            console.error('Update profile failed', e);
        }
    };

    const navigationItems = [
        { id: 'overview', label: 'Overview', icon: Eye, active: activeTab === 'overview' },
        { id: 'profile', label: 'My Profile', icon: UserIcon, active: activeTab === 'profile' },
        { id: 'policy-view', label: 'Policy View', icon: FileText, active: activeTab === 'policy-view' },
        { id: 'payment-history', label: 'Payment History', icon: DollarSign, active: activeTab === 'payment-history' },
        { id: 'medical-insurance', label: 'Medical Insurance', icon: Shield, active: activeTab === 'medical-insurance' },
        { id: 'referrer', label: 'Referrer', icon: Crown, active: activeTab === 'referrer' },
    ];

    // Map API payment history to UI rows (now using gateway payments)
    const paymentHistoryData: PaymentData[] = (gatewayPayments || []).map((g: any) => ({
        description: g.description || 'Medical Insurance Payment',
        amount: Number(g.amount).toFixed(2),
        method: 'Card',
        status: g.status,
        date: new Date(g.completed_at || g.created_at).toLocaleString(),
    }));

    const commissionData = [
        { month: "August 2025", status: "Pending", amount: "1098.96" },
        { month: "July 2025", status: "Pending", amount: "1110.73" },
        { month: "July 2025", status: "Pending", amount: "213.52" },
        { month: "June 2025", status: "Paid", amount: "1452.75" },
        { month: "May 2025", status: "Paid", amount: "832.48" },
        { month: "April 2025", status: "Paid", amount: "948.41" },
        { month: "March 2025", status: "Paid", amount: "707.89" },
        { month: "February 2025", status: "Paid", amount: "370.08" }
    ];

    const renderTabContent = () => {
        switch (activeTab) {
            case 'overview':
                return (
                    <div className="space-y-6">
                        <div className="flex items-center justify-between">
                            <h2 className="text-2xl font-bold text-gray-800">Account Overview</h2>
                            <div className="flex items-center gap-2 text-sm text-gray-500">
                                <Clock className="w-4 h-4" />
                                <span>Last updated: {new Date().toLocaleTimeString()}</span>
                            </div>
                        </div>

                        {isOverviewLoading ? (
                            <div className="flex items-center justify-center py-12">
                                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <span className="ml-2 text-gray-600">Loading overview data...</span>
                            </div>
                        ) : (
                            <>
                                {/* Key Metrics Cards */}
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <div className="text-sm opacity-90">Agent Code</div>
                                                <div className="text-2xl font-bold mt-1">{user?.agent_code || 'N/A'}</div>
                                            </div>
                                            <UserIcon className="w-8 h-8 opacity-80" />
                                        </div>
                                    </div>
                                    
                                    <div className="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <div className="text-sm opacity-90">Total Commission</div>
                                                <div className="text-2xl font-bold mt-1">
                                                    RM {dashboardStats?.total_commission_earned ? parseFloat(dashboardStats.total_commission_earned).toLocaleString() : '0.00'}
                                                </div>
                                            </div>
                                            <DollarSign className="w-8 h-8 opacity-80" />
                                        </div>
                                    </div>
                                    
                                    <div className="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <div className="text-sm opacity-90">Network Level</div>
                                                <div className="text-2xl font-bold mt-1">{user?.mlm_level || 0}</div>
                                            </div>
                                            <Crown className="w-8 h-8 opacity-80" />
                                        </div>
                                    </div>
                                    
                                    <div className="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <div className="text-sm opacity-90">Total Members</div>
                                                <div className="text-2xl font-bold mt-1">{dashboardStats?.total_members || 0}</div>
                                            </div>
                                            <Users className="w-8 h-8 opacity-80" />
                                        </div>
                                    </div>
                                </div>

                                {/* Performance Metrics */}
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    {/* Commission Trend Chart */}
                                    <div className="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                        <div className="flex items-center justify-between mb-4">
                                            <h3 className="text-lg font-semibold text-gray-800">Commission Trend</h3>
                                            <BarChart3 className="w-5 h-5 text-gray-500" />
                                        </div>
                                        <div className="h-64">
                                            {commissionHistory.length > 0 ? (
                                                <Line
                                                    data={{
                                                        labels: commissionHistory.slice(0, 6).map((item: any) => 
                                                            new Date(item.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
                                                        ),
                                                        datasets: [{
                                                            label: 'Commission (RM)',
                                                            data: commissionHistory.slice(0, 6).map((item: any) => parseFloat(item.amount || 0)),
                                                            borderColor: 'rgb(59, 130, 246)',
                                                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                                            tension: 0.4,
                                                            fill: true
                                                        }]
                                                    }}
                                                    options={{
                                                        responsive: true,
                                                        maintainAspectRatio: false,
                                                        plugins: {
                                                            legend: {
                                                                display: false
                                                            }
                                                        },
                                                        scales: {
                                                            y: {
                                                                beginAtZero: true,
                                                                grid: {
                                                                    color: 'rgba(0, 0, 0, 0.05)'
                                                                }
                                                            },
                                                            x: {
                                                                grid: {
                                                                    display: false
                                                                }
                                                            }
                                                        }
                                                    }}
                                                />
                                            ) : (
                                                <div className="flex items-center justify-center h-full text-gray-500">
                                                    No commission data available
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {/* Target Achievement */}
                                    <div className="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                        <div className="flex items-center justify-between mb-4">
                                            <h3 className="text-lg font-semibold text-gray-800">Target Achievement</h3>
                                            <Target className="w-5 h-5 text-gray-500" />
                                        </div>
                                        <div className="space-y-4">
                                            <div className="text-center">
                                                <div className="text-3xl font-bold text-gray-800 mb-2">
                                                    {dashboardStats?.target_achievement || 0}%
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    of monthly target achieved
                                                </div>
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-3">
                                                <div 
                                                    className="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500"
                                                    style={{ width: `${Math.min(dashboardStats?.target_achievement || 0, 100)}%` }}
                                                ></div>
                                            </div>
                                            <div className="flex justify-between text-sm text-gray-600">
                                                <span>Target: RM {dashboardStats?.commission_target || '0.00'}</span>
                                                <span>Achieved: RM {dashboardStats?.monthly_commission || '0.00'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Wallet Balance and Recent Activities */}
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    {/* Wallet Balance */}
                                    <div className="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                        <div className="flex items-center justify-between mb-4">
                                            <h3 className="text-lg font-semibold text-gray-800">Wallet Balance</h3>
                                            <Wallet className="w-5 h-5 text-gray-500" />
                                        </div>
                                        <div className="text-center">
                                            <div className="text-3xl font-bold text-gray-800 mb-2">
                                                RM {walletData?.balance ? parseFloat(walletData.balance).toLocaleString() : '0.00'}
                                            </div>
                                            <div className="text-sm text-gray-600 mb-4">Available Balance</div>
                                            <div className="flex gap-2">
                                                <button className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                                    Withdraw
                                                </button>
                                                <button className="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                                                    View History
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Recent Activities */}
                                    <div className="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                        <div className="flex items-center justify-between mb-4">
                                            <h3 className="text-lg font-semibold text-gray-800">Recent Activities</h3>
                                            <Activity className="w-5 h-5 text-gray-500" />
                                        </div>
                                        <div className="space-y-3">
                                            {recentActivities.slice(0, 4).map((activity, index) => (
                                                <div key={index} className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                    <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <Activity className="w-4 h-4 text-blue-600" />
                                                    </div>
                                                    <div className="flex-1 min-w-0">
                                                        <div className="text-sm font-medium text-gray-800 truncate">
                                                            {activity.description}
                                                        </div>
                                                        <div className="text-xs text-gray-500">
                                                            {new Date(activity.created_at).toLocaleDateString()}
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                            {recentActivities.length === 0 && (
                                                <div className="text-center text-gray-500 py-4">
                                                    No recent activities
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </>
                        )}
                    </div>
                );

            case 'profile':
                return (
                    <div className="space-y-4">
                        <div className="flex items-center gap-2">
                            <h2 className="text-xl font-bold text-gray-800">My Profile</h2>
                            <div className="h-0.5 w-8 bg-emerald-500 rounded-full"></div>
                        </div>
                        
                        <div className="space-y-4">
                            <div className="rounded-lg border border-gray-200">
                                <div className="bg-emerald-50 px-4 py-3 font-medium text-sm text-emerald-800">General Info</div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                                    <div>
                                        <div className="text-gray-500 text-sm mb-1">Name</div>
                                        <div className="font-semibold text-gray-800">{user?.name || 'N/A'}</div>
                                    </div>
                                    <div>
                                        <div className="text-gray-500 text-sm mb-1">Phone Number</div>
                                        <div className="font-semibold text-gray-800">{user?.phone_number || 'N/A'}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div className="rounded-lg border border-gray-200">
                                <div className="bg-emerald-50 px-4 py-3 font-medium text-sm text-emerald-800">Address Info</div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                                    <div className="md:col-span-2">
                                        <div className="text-gray-500 text-sm mb-1">Address</div>
                                        <div className="font-semibold text-gray-800">{user?.address || '-'}</div>
                                    </div>
                                    <div>
                                        <div className="text-gray-500 text-sm mb-1">Postal Code</div>
                                        <div className="font-semibold text-gray-800">{user?.postal_code || '-'}</div>
                                    </div>
                                    <div>
                                        <div className="text-gray-500 text-sm mb-1">City</div>
                                        <div className="font-semibold text-gray-800">{user?.city || '-'}</div>
                                    </div>
                                    <div>
                                        <div className="text-gray-500 text-sm mb-1">State</div>
                                        <div className="font-semibold text-gray-800">{user?.state || '-'}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div className="flex flex-col sm:flex-row gap-3">
                                <button 
                                    onClick={handleUpdateProfile}
                                    className="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    Update Profile
                                </button>
                                <button 
                                    onClick={handleChangePhone}
                                    className="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    Change Phone Number
                                </button>
                                <button 
                                    onClick={handleChangePassword}
                                    className="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </div>
                );

            case 'policy-view':
                return (
                    <div className="space-y-4">
                        <div className="flex items-center gap-2">
                            <h2 className="text-xl font-bold text-gray-800">Insurance Plans</h2>
                            <div className="h-0.5 w-8 bg-emerald-500 rounded-full"></div>
                        </div>
                        
                        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div className="text-sm text-blue-800">
                                <p className="mb-3">
                                    View detailed policy information for our comprehensive insurance plans. 
                                    Click on any plan to access the full policy document with terms, conditions, and coverage details.
                                </p>
                            </div>
                        </div>
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {/* MediPlan Coop */}
                            <div className="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                                        <Shield className="w-6 h-6 text-white" />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-gray-800">MediPlan Coop</h3>
                                        <p className="text-sm text-gray-600">Medical Cooperative Plan</p>
                                    </div>
                                </div>
                                <div className="space-y-2 mb-4">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">Annual Limit</span>
                                        <span className="font-medium">RM 1,000,000</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">Room & Board</span>
                                        <span className="font-medium">RM 250/day</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">Panel Hospitals</span>
                                        <span className="font-medium">250</span>
                                    </div>
                                </div>
                                <button
                                    onClick={() => handleViewPolicy({
                                        name: 'MediPlan Coop',
                                        type: 'Medical Cooperative Plan',
                                        color: 'from-blue-600 to-cyan-600',
                                        details: {
                                            annualLimit: 'RM 1,000,000',
                                            roomBoard: 'RM 250/day',
                                            panelHospitals: '250',
                                            panelClinics: '4,000',
                                            tpa: 'eMAS (Eximius Medical Administration Solutions)',
                                            ageEligibility: '30 days to 45 years (renewal up to 100)',
                                            waitingPeriod: '90 days general, 180 days specific',
                                            benefits: [
                                                'Room & Board: RM250 per day',
                                                'Ambulance Fees: Included',
                                                'Intensive Care Unit: Included',
                                                'Hospital Supplies & Services: Included',
                                                'Surgical Fees: Included',
                                                'Operating Theater Fees: Included',
                                                'Anesthetist Fees: Included',
                                                'In-hospital Doctor Visit: Included',
                                                'Day Care & Day Surgery: Included',
                                                'Second Surgical Opinion: Included',
                                                'Emergency Accidental Dental: Included',
                                                'Covid Test for Admission: Included',
                                                'Government Hospital Allowance: RM100/day',
                                                'Pre-Hospital Diagnostic: RM5,000',
                                                'Accidental Injury Surgery: RM10,000',
                                                'Bereavement: RM10,000',
                                                'Outpatient Cancer Treatment: RM100,000',
                                                'Conditional Outpatient Benefits: As Charged'
                                            ]
                                        }
                                    })}
                                    className="w-full bg-gradient-to-r from-blue-600 to-cyan-600 text-white py-2 px-4 rounded-lg font-medium hover:from-blue-700 hover:to-cyan-700 transition-all duration-300 flex items-center justify-center group"
                                >
                                    <FileText className="w-4 h-4 mr-2 group-hover:translate-x-1 transition-transform" />
                                    View Policy
                                            </button>
                            </div>

                            {/* Senior Care Plan Gold 270 */}
                            <div className="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="w-10 h-10 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center">
                                        <Shield className="w-6 h-6 text-white" />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-gray-800">Senior Care Gold 270</h3>
                                        <p className="text-sm text-gray-600">Senior Care Plan</p>
                                    </div>
                                </div>
                                <div className="space-y-2 mb-4">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">Annual Limit</span>
                                        <span className="font-medium">RM 75,000</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">Room & Board</span>
                                        <span className="font-medium">RM 270/day</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">Panel Hospitals</span>
                                        <span className="font-medium">148</span>
                                    </div>
                                </div>
                                <button
                                    onClick={() => handleViewPolicy({
                                        name: 'Senior Care Plan Gold 270',
                                        type: 'Senior Care Plan',
                                        color: 'from-yellow-600 to-orange-600',
                                        details: {
                                            annualLimit: 'RM 75,000',
                                            roomBoard: 'RM 270/day',
                                            panelHospitals: '148',
                                            tpa: 'MiCare',
                                            ageEligibility: '46-65 years (renewal up to 70)',
                                            pricing: {
                                                monthly: 'RM 150 + RM 150 Commitment',
                                                quarterly: 'RM 450',
                                                semiAnnually: 'RM 900',
                                                annually: 'RM 1,800'
                                            },
                                            benefits: [
                                                'Hospital Room & Board: RM270/day (max 180 days)',
                                                'Intensive Care Unit: Full Reimbursement',
                                                'Hospital Supplies and Services: Included',
                                                'Surgeon Fee: Included',
                                                'Anaesthetist Fee: Included',
                                                'Operating Theatre Charges: Included',
                                                'Daily Physician Visit: Included',
                                                'Pre-Hospital Diagnostic Tests: Included',
                                                'Pre-Hospitalization Consultation: Included',
                                                'Second Surgical Opinion: Included',
                                                'Post-Hospitalization Treatment: Included',
                                                'Emergency Accidental Outpatient: Included',
                                                'Outpatient Cancer Treatment: Included',
                                                'Outpatient Kidney Dialysis: Included',
                                                'Daycare Procedure: Included',
                                                'Ambulance Charges: Included',
                                                'Government Hospital Allowance: RM100/day',
                                                'Medical Report Fee: RM80',
                                                'Funeral Expenses: RM10,000'
                                            ]
                                        }
                                    })}
                                    className="w-full bg-gradient-to-r from-yellow-600 to-orange-600 text-white py-2 px-4 rounded-lg font-medium hover:from-yellow-700 hover:to-orange-700 transition-all duration-300 flex items-center justify-center group"
                                >
                                    <FileText className="w-4 h-4 mr-2 group-hover:translate-x-1 transition-transform" />
                                    View Policy
                                            </button>
                                        </div>

                            {/* Senior Care Plan Diamond 370 */}
                            <div className="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                                        <Shield className="w-6 h-6 text-white" />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-gray-800">Senior Care Diamond 370</h3>
                                        <p className="text-sm text-gray-600">Premium Senior Care</p>
                                </div>
                            </div>
                                <div className="space-y-2 mb-4">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">Annual Limit</span>
                                        <span className="font-medium">RM 100,000</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">Room & Board</span>
                                        <span className="font-medium">RM 370/day</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">Panel Hospitals</span>
                                        <span className="font-medium">148</span>
                                    </div>
                                </div>
                                <button
                                    onClick={() => handleViewPolicy({
                                        name: 'Senior Care Plan Diamond 370',
                                        type: 'Premium Senior Care',
                                        color: 'from-purple-600 to-pink-600',
                                        details: {
                                            annualLimit: 'RM 100,000',
                                            roomBoard: 'RM 370/day',
                                            panelHospitals: '148',
                                            tpa: 'MiCare',
                                            ageEligibility: '46-65 years (renewal up to 70)',
                                            pricing: {
                                                monthly: 'RM 210 + RM 210 Commitment',
                                                quarterly: 'RM 630',
                                                semiAnnually: 'RM 1,260',
                                                annually: 'RM 2,520'
                                            },
                                            benefits: [
                                                'Hospital Room & Board: RM370/day (max 180 days)',
                                                'Intensive Care Unit: Full Reimbursement',
                                                'Hospital Supplies and Services: Included',
                                                'Surgeon Fee: Included',
                                                'Anaesthetist Fee: Included',
                                                'Operating Theatre Charges: Included',
                                                'Daily Physician Visit: Included',
                                                'Pre-Hospital Diagnostic Tests: Included',
                                                'Pre-Hospitalization Consultation: Included',
                                                'Second Surgical Opinion: Included',
                                                'Post-Hospitalization Treatment: Included',
                                                'Emergency Accidental Outpatient: Included',
                                                'Outpatient Cancer Treatment: Included',
                                                'Outpatient Kidney Dialysis: Included',
                                                'Daycare Procedure: Included',
                                                'Ambulance Charges: Included',
                                                'Government Hospital Allowance: RM200/day',
                                                'Medical Report Fee: RM80',
                                                'Funeral Expenses: RM10,000'
                                            ]
                                        }
                                    })}
                                    className="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-2 px-4 rounded-lg font-medium hover:from-purple-700 hover:to-pink-700 transition-all duration-300 flex items-center justify-center group"
                                >
                                    <FileText className="w-4 h-4 mr-2 group-hover:translate-x-1 transition-transform" />
                                    View Policy
                                </button>
                            </div>
                        </div>
                    </div>
                );

            case 'medical-insurance':
                return (
                        <div className="space-y-4">
                        {/* Compact Header */}
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-8 h-8 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                    <Shield className="w-4 h-4 text-white" />
                                    </div>
                                <div>
                                    <h2 className="text-lg font-bold text-gray-800">Medical Insurance Clients</h2>
                                    <p className="text-xs text-gray-500">{clients.length} total clients</p>
                                </div>
                            </div>
                            <button
                                onClick={handleMedicalInsuranceRegistration}
                                className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium flex items-center gap-2"
                            >
                                <Plus className="w-4 h-4" />
                                Add Client
                            </button>
                        </div>

                        {/* Compact Search and Stats */}
                        <div className="bg-white rounded-lg border border-gray-200 p-4">
                            <div className="flex flex-col sm:flex-row gap-3">
                                    <div className="flex-1">
                                    <div className="relative">
                                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                                        <input
                                            type="text"
                                            placeholder="Search clients..."
                                            value={searchQuery}
                                            onChange={handleSearchChange}
                                            className="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                        />
                                    </div>
                                </div>
                                <div className="flex gap-2 text-xs">
                                    <span className="bg-emerald-100 text-emerald-700 px-2 py-1 rounded">
                                        {clients.length} Total
                                    </span>
                                    <span className="bg-blue-100 text-blue-700 px-2 py-1 rounded">
                                        {filteredClients.length} Found
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Loading and Error States */}
                        {isLoading && (
                            <div className="flex items-center justify-center py-8">
                                <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-emerald-600"></div>
                                <span className="ml-2 text-sm text-gray-600">Loading...</span>
                            </div>
                        )}
                        
                        {error && (
                            <div className="bg-red-50 border border-red-200 rounded-lg p-3">
                                <div className="flex items-center text-sm">
                                    <XCircle className="w-4 h-4 text-red-600 mr-2" />
                                    <span className="text-red-800">{error}</span>
                                </div>
                            </div>
                        )}

                        {/* Compact Clients List */}
                        {!isLoading && !error && (
                            <div className="space-y-3">
                                {paginatedClients.length === 0 ? (
                                    <div className="bg-white border border-gray-200 rounded-lg p-8 text-center">
                                        <Shield className="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                        <h3 className="text-lg font-semibold text-gray-800 mb-2">
                                            {searchQuery ? 'No matching clients' : 'No clients yet'}
                                        </h3>
                                        <p className="text-sm text-gray-600 mb-4">
                                            {searchQuery ? 'Try different search terms' : 'Register your first client to get started.'}
                                        </p>
                                        {!searchQuery && (
                                            <button
                                                onClick={handleMedicalInsuranceRegistration}
                                                className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm"
                                            >
                                                Register Client
                                            </button>
                                        )}
                                    </div>
                                ) : (
                                    <>
                                        {/* Compact Client Cards */}
                                        <div className="space-y-3">
                                            {paginatedClients.map((client, index) => (
                                                <div key={client.id || index} className="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200 hover:border-emerald-200">
                                                    <div className="flex items-start justify-between gap-3">
                                                        {/* Client Avatar and Basic Info */}
                                                        <div className="flex items-start gap-3 flex-1">
                                                            <div className="w-10 h-10 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                                                                {client.full_name?.charAt(0) || 'C'}
                                                            </div>
                                                            <div className="flex-1 min-w-0">
                                                                <div className="flex items-center gap-2 mb-1">
                                                                    <h3 className="text-base font-semibold text-gray-800 truncate">{client.full_name}</h3>
                                                                    <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                                                                        client.status === 'active' 
                                                                            ? 'bg-green-100 text-green-800' 
                                                                            : 'bg-gray-100 text-gray-800'
                                                                    }`}>
                                                                        {client.status}
                                                                    </span>
                                                                </div>
                                                                <p className="text-xs text-gray-600 mb-2">NRIC: {client.nric}</p>
                                                                
                                                                {/* Compact Info Grid */}
                                                                <div className="grid grid-cols-2 gap-3 text-xs">
                                                                    <div>
                                                                        <span className="text-gray-500">Plan:</span>
                                                                        <div className="font-medium text-gray-800 truncate">{client.plan_name}</div>
                                                                    </div>
                                                                    <div>
                                                                        <span className="text-gray-500">Phone:</span>
                                                                        <div className="font-medium text-gray-800">{client.phone_number}</div>
                                                                    </div>
                                                                    <div>
                                                                        <span className="text-gray-500">Card:</span>
                                                                        <div className="font-medium text-gray-800">{client.medical_card_type}</div>
                                                                    </div>
                                                                    <div>
                                                                        <span className="text-gray-500">Payment:</span>
                                                                        <div className="font-medium text-gray-800">
                                                                            {gatewayPayments.find((p: any) => p.client_id === client.id) ? '✓ Recorded' : 'Pending'}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        {/* Compact Action Buttons */}
                                                        <div className="flex gap-1">
                                                            <button 
                                                                onClick={() => handleViewClientDetails(client)} 
                                                                className="p-2 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition-colors"
                                                                title="View Details"
                                                            >
                                                                <Eye className="w-4 h-4" />
                                                            </button>
                                                            <button 
                                                                onClick={() => handleDownloadClientCard(client)} 
                                                                className="p-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                                                                title="Download Card"
                                                            >
                                                                <Download className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                            ))}
                            </div>
                            
                                        {/* Compact Pagination */}
                                        {totalPages > 1 && (
                                            <div className="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-3">
                                                <div className="text-xs text-gray-600">
                                                    {startIndex + 1}-{Math.min(endIndex, filteredClients.length)} of {filteredClients.length}
                                    </div>
                                                
                                                <div className="flex items-center gap-1">
                                                    <button
                                                        onClick={() => handlePageChange(currentPage - 1)}
                                                        disabled={currentPage === 1}
                                                        className="px-2 py-1 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    >
                                                        ←
                                            </button>
                                                    
                                                    <div className="flex gap-1">
                                                        {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                                                            const page = i + 1;
                                                            return (
                                                                <button
                                                                    key={page}
                                                                    onClick={() => handlePageChange(page)}
                                                                    className={`px-2 py-1 text-xs font-medium rounded ${
                                                                        page === currentPage
                                                                            ? 'bg-emerald-600 text-white'
                                                                            : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                                                                    }`}
                                                                >
                                                                    {page}
                                            </button>
                                                            );
                                                        })}
                                        </div>
                                                    
                                                    <button
                                                        onClick={() => handlePageChange(currentPage + 1)}
                                                        disabled={currentPage === totalPages}
                                                        className="px-2 py-1 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    >
                                                        →
                                                    </button>
                                    </div>
                                </div>
                                        )}
                                    </>
                                )}
                            </div>
                            )}
                    </div>
                );

            case 'payment-history':
                return (
                    <div className="space-y-4">
                        <div className="flex items-center gap-2">
                            <h2 className="text-xl font-bold text-gray-800">Payment History</h2>
                            <div className="h-0.5 w-8 bg-emerald-500 rounded-full"></div>
                        </div>
                        
                        <div className="space-y-3">
                            <div className="block md:hidden">
                                {paymentHistoryData.map((payment, index) => (
                                    <div key={index} className="bg-white border border-gray-200 rounded-lg p-3 mb-3">
                                        <div className="space-y-2">
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1 min-w-0">
                                                    <div className="text-sm font-medium text-gray-800 truncate">
                                                        {payment.description}
                                                    </div>
                                                    <div className="text-xs text-gray-500 mt-1">
                                                        {payment.date}
                                                    </div>
                                                </div>
                                                <div className="text-right ml-2">
                                                    <div className="text-sm font-semibold text-gray-800">
                                                        RM {payment.amount}
                                                    </div>
                                                    <div className="text-xs text-gray-500">
                                                        {payment.method}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center justify-between pt-2 border-t border-gray-100">
                                                <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {payment.status}
                                                </span>
                                                <button 
                                                    onClick={() => handlePrintReceipt(payment)}
                                                    className="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-xs hover:bg-emerald-200 transition-colors"
                                                >
                                                    <Printer className="w-3 h-3" />
                                                    Print
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="hidden md:block overflow-x-auto">
                                <table className="min-w-full whitespace-nowrap">
                                    <thead>
                                        <tr className="border-b border-gray-200">
                                            <th className="text-left py-2 px-3 text-xs font-medium text-gray-600">DESCRIPTION</th>
                                            <th className="text-left py-2 px-3 text-xs font-medium text-gray-600">AMOUNT</th>
                                            <th className="text-left py-2 px-3 text-xs font-medium text-gray-600">METHOD</th>
                                            <th className="text-left py-2 px-3 text-xs font-medium text-gray-600">STATUS</th>
                                            <th className="text-left py-2 px-3 text-xs font-medium text-gray-600">DATE</th>
                                            <th className="text-left py-2 px-3 text-xs font-medium text-gray-600">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {paymentHistoryData.map((payment, index) => (
                                            <tr key={index} className="hover:bg-gray-50">
                                                <td className="py-2 px-3 text-xs text-gray-800 min-w-[250px]">
                                                    {payment.description}
                                                </td>
                                                <td className="py-2 px-3 text-xs font-medium text-gray-800">
                                                    RM {payment.amount}
                                                </td>
                                                <td className="py-2 px-3 text-xs text-gray-600">
                                                    {payment.method}
                                                </td>
                                                <td className="py-2 px-3">
                                                    <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {payment.status}
                                                    </span>
                                                </td>
                                                <td className="py-2 px-3 text-xs text-gray-600">
                                                    {payment.date}
                                                </td>
                                                <td className="py-2 px-3">
                                                    <button 
                                                        onClick={() => handlePrintReceipt(payment)}
                                                        className="inline-flex items-center gap-1 px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs hover:bg-emerald-200 transition-colors"
                                                    >
                                                        <Printer className="w-3 h-3" />
                                                        Print
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            {isLoading && <div className="text-sm text-gray-500 px-1">Loading...</div>}
                            {error && <div className="text-sm text-red-600 px-1">{error}</div>}
                        </div>
                    </div>
                );

            case 'referrer':
                return (
                    <div className="space-y-4">
                        <div className="flex items-center gap-2">
                            <h2 className="text-xl font-bold text-gray-800">Referral Program</h2>
                            <div className="h-0.5 w-8 bg-emerald-500 rounded-full"></div>
                        </div>
                        
                        <div className="flex flex-wrap gap-2 mb-6">
                            {[
                                { id: 'referral', label: 'Referral' },
                                { id: 'bank-info', label: 'Bank Info' },
                                { id: 'commission', label: 'Commission' }
                            ].map((tab) => (
                                <button
                                    key={tab.id}
                                    onClick={() => setActiveReferrerTab(tab.id as ReferrerSubTab)}
                                    className={`px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 ${
                                        activeReferrerTab === tab.id
                                            ? 'bg-emerald-600 text-white shadow-md'
                                            : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'
                                    }`}
                                >
                                    {tab.label}
                                </button>
                            ))}
                        </div>

                        {activeReferrerTab === 'referral' && (
                            <div className="space-y-6">
                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="font-semibold text-gray-800">My Referral Link</h3>
                                        <button
                                            onClick={handleMedicalInsuranceRegistration}
                                            className="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                        >
                                            <Plus className="w-4 h-4" />
                                            Register
                                        </button>
                                    </div>
                                    <div className="space-y-3">
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                value={`https://app.wekongsi.com/auth/register/${user?.agent_code || ''}`}
                                                readOnly
                                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm"
                                            />
                                            <button className="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors">
                                                Copy Referral Link
                                            </button>
                                        </div>
                                        <div className="flex gap-2">
                                            <button className="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                <Grid3X3 className="w-4 h-4 text-gray-600" />
                                            </button>
                                            <button className="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                <Share2 className="w-4 h-4 text-gray-600" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 className="font-semibold text-gray-800 mb-4">My Landing Page</h3>
                                    <div className="space-y-3">
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                value={`https://www.wekongsi.com/agent/${user?.agent_code || ''}`}
                                                readOnly
                                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm"
                                            />
                                            <button className="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors">
                                                Copy Landing Page
                                            </button>
                                        </div>
                                        <div className="flex gap-2">
                                            <button className="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                <Grid3X3 className="w-4 h-4 text-gray-600" />
                                            </button>
                                            <button className="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                <Share2 className="w-4 h-4 text-gray-600" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {/* External Medical Insurance Registration Link */}
                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="font-semibold text-gray-800">Medical Insurance Registration Link</h3>
                                        <div className="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            External Registration
                                        </div>
                                    </div>
                                    <div className="space-y-3">
                                        <p className="text-sm text-gray-600">
                                            Share this link to allow clients to register for medical insurance directly under your agent code.
                                        </p>
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                value={`${typeof window !== 'undefined' ? window.location.origin : ''}/register-external?agent_code=${user?.agent_code || ''}`}
                                                readOnly
                                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm"
                                            />
                                            <button 
                                                onClick={() => {
                                                    if (typeof window !== 'undefined') {
                                                        navigator.clipboard.writeText(`${window.location.origin}/register-external?agent_code=${user?.agent_code || ''}`);
                                                        // You could add a toast notification here
                                                    }
                                                }}
                                                className="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors"
                                            >
                                                Copy Link
                                            </button>
                                        </div>
                                        <div className="flex gap-2">
                                            <button 
                                                onClick={() => {
                                                    if (typeof window !== 'undefined') {
                                                        const url = `${window.location.origin}/register-external?agent_code=${user?.agent_code || ''}`;
                                                        window.open(`https://wa.me/?text=${encodeURIComponent(`Register for Medical Insurance: ${url}`)}`, '_blank');
                                                    }
                                                }}
                                                className="p-2 border border-green-300 rounded-lg hover:bg-green-50 transition-colors"
                                                title="Share via WhatsApp"
                                            >
                                                <svg className="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                                </svg>
                                            </button>
                                            <button 
                                                onClick={() => {
                                                    if (typeof window !== 'undefined') {
                                                        const url = `${window.location.origin}/register-external?agent_code=${user?.agent_code || ''}`;
                                                        window.open(`https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent('Register for Medical Insurance')}`, '_blank');
                                                    }
                                                }}
                                                className="p-2 border border-blue-300 rounded-lg hover:bg-blue-50 transition-colors"
                                                title="Share via Telegram"
                                            >
                                                <svg className="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <h4 className="font-semibold text-gray-800">Account Overview</h4>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white">
                                            <div className="text-sm opacity-90">Direct Referral</div>
                                            <div className="text-3xl font-bold mt-2">14</div>
                                        </div>
                                        <div className="bg-white rounded-xl p-6 border border-gray-200">
                                            <div className="text-sm text-gray-600">Total Commission</div>
                                            <div className="text-3xl font-bold text-gray-800 mt-2">RM 14,378.48</div>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <h4 className="font-semibold text-gray-800">Referrals Breakdown</h4>
                                    <div className="bg-white border border-gray-200 rounded-lg p-4">
                                        <div className="flex flex-col sm:flex-row gap-4 mb-4">
                                            <div className="flex-1">
                                                <div className="relative">
                                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                                    <input
                                                        type="text"
                                                        placeholder="Search name or phone numb"
                                                        className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm"
                                                    />
                                                </div>
                                            </div>
                                            <select className="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                                                <option>All Status</option>
                                            </select>
                                        </div>
                                        
                                        <div className="flex flex-wrap gap-4 mb-4 text-sm">
                                            <div className="flex items-center gap-2">
                                                <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                                <span>Active</span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <div className="w-3 h-3 bg-orange-500 rounded-full"></div>
                                                <span>Pending Payment, Probation</span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <div className="w-3 h-3 bg-red-500 rounded-full"></div>
                                                <span>Suspended, Terminated, Probation Rejected</span>
                                            </div>
                                        </div>

                                        <div className="space-y-3">
                                            <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div className="flex items-center gap-3">
                                                    <span className="font-medium">Level 1</span>
                                                    <div className="flex gap-1">
                                                        <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                                        <div className="w-2 h-2 bg-orange-500 rounded-full"></div>
                                                        <div className="w-2 h-2 bg-red-500 rounded-full"></div>
                                                    </div>
                                                    <span className="text-sm text-gray-600">13, 1, 0</span>
                                                </div>
                                                <ChevronDown className="w-4 h-4 text-gray-600" />
                                            </div>
                                            
                                            <div className="space-y-2 ml-4">
                                                {[
                                                    { name: "MOHD SHARULNIZAM BIN MOHD SALEH", phone: "+60173014642", status: "Active", createdAt: "2024-06-05 03:45 PM", dots: ["green", "yellow"] },
                                                    { name: "ZUL AZRI BIN MOHD KHAMIS @ MOHD GENDOT", phone: "", status: "Active", createdAt: "2024-06-05 12:53 PM", dots: ["green"] },
                                                    { name: "MOHD AZRUL BIN WAHIT", phone: "", status: "Active", createdAt: "2024-06-05 12:56 AM", dots: ["green"] },
                                                    { name: "ROSLINA BINTI MAT SAID", phone: "", status: "Active", createdAt: "2024-05-16 11:46 PM", dots: ["green"] },
                                                    { name: "SYAFIEQ ZAILAN", phone: "", status: "Active", createdAt: "2024-04-28 06:48 AM", dots: ["green", "red"] },
                                                    { name: "NOOR SHEIKHA @ AZIL BIN MOHAMED AZMI", phone: "", status: "Active", createdAt: "2024-04-04 09:30 PM", dots: ["green"] },
                                                    { name: "NURFADZILINDA BINTI MD AKHIR", phone: "", status: "Active", createdAt: "2024-04-04 01:27 AM", dots: ["green"] },
                                                    { name: "MUHAMMAD RASHIDI BIN KHAIRUL OMAR KUMAR", phone: "", status: "Active", createdAt: "2024-03-31 09:51 PM", dots: ["green"] },
                                                    { name: "ZULHAIZAL BIN AHAMAD PAZIR", phone: "", status: "Active", createdAt: "2024-03-27 12:58 AM", dots: ["green"] },
                                                    { name: "DERIC TING", phone: "", status: "Active", createdAt: "2024-03-27 12:27 AM", dots: ["green", "yellow", "red"] },
                                                    { name: "MUHAMMAD MUSLIM BIN MAT SAMSUDIN", phone: "", status: "Active", createdAt: "2024-03-23 09:51 PM", dots: ["green"] },
                                                    { name: "MUHAMMAD ADZIM BIN SHAMSUL ANUAR", phone: "", status: "Active", createdAt: "2024-03-19 01:17 AM", dots: ["green"] },
                                                    { name: "NOOR LIYANA BT KHAIRUL OMAR KUMAR", phone: "", status: "Active", createdAt: "2024-03-18 10:37 PM", dots: ["green", "red", "red"] },
                                                    { name: "MOHD FUAD BIN SANUSI", phone: "", status: "Active", createdAt: "2024-03-04 07:51 PM", dots: ["green", "yellow"] }
                                                ].map((member, index) => (
                                                    <div key={index} className="bg-white border border-gray-100 rounded-lg p-3">
                                                        <div className="flex items-start justify-between">
                                                            <div className="flex-1 min-w-0">
                                                                <div className="font-medium text-gray-800 text-sm truncate">{member.name}</div>
                                                                {member.phone && (
                                                                    <div className="text-xs text-gray-500 mt-1">{member.phone}</div>
                                                                )}
                                                                <div className="text-xs text-gray-500 mt-1">Created At: {member.createdAt}</div>
                                                            </div>
                                                            <div className="flex items-center gap-2 ml-3">
                                                                <div className="flex gap-1">
                                                                    {member.dots.map((dot, dotIndex) => (
                                                                        <div 
                                                                            key={dotIndex} 
                                                                            className={`w-2 h-2 rounded-full ${
                                                                                dot === 'green' ? 'bg-green-500' : 
                                                                                dot === 'yellow' ? 'bg-orange-500' : 'bg-red-500'
                                                                            }`}
                                                                        />
                                                                    ))}
                                                                </div>
                                                                <button className="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs hover:bg-emerald-200 transition-colors">
                                                                    View User
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                        
                                        <div className="space-y-2 mt-4">
                                            {[
                                                { level: 2, active: 99, pending: 23, suspended: 23 },
                                                { level: 3, active: 46, pending: 34, suspended: 34 },
                                                { level: 4, active: 75, pending: 27, suspended: 28 },
                                                { level: 5, active: 107, pending: 90, suspended: 93 },
                                                { level: 6, active: 203, pending: 135, suspended: 138 },
                                                { level: 7, active: 139, pending: 80, suspended: 80 },
                                                { level: 8, active: 118, pending: 35, suspended: 35 },
                                                { level: 9, active: 38, pending: 4, suspended: 4 },
                                                { level: 10, active: 55, pending: 2, suspended: 2 },
                                                { level: 11, active: 6, pending: 0, suspended: 0 },
                                                { level: 12, active: 8, pending: 1, suspended: 0 }
                                            ].map((levelData, index) => (
                                                <div key={index} className="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                                    <div className="flex items-center gap-2">
                                                        <span className="font-medium text-sm">Level {levelData.level}</span>
                                                        <div className="flex gap-1">
                                                            <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                                            <div className="w-2 h-2 bg-orange-500 rounded-full"></div>
                                                            <div className="w-2 h-2 bg-red-500 rounded-full"></div>
                                                        </div>
                                                        <span className="text-xs text-gray-600">{levelData.active} Active, {levelData.pending} Pending/Probation, {levelData.suspended} Suspended/Terminated/Rejected</span>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {activeReferrerTab === 'bank-info' && (
                            <div className="space-y-4">
                                <h3 className="font-semibold text-gray-800">Bank Profile</h3>
                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <div className="text-gray-500 text-sm mb-1">Bank Name</div>
                                            <div className="font-semibold text-gray-800">{user?.bank_name || '-'}</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-500 text-sm mb-1">Owner Name</div>
                                            <div className="font-semibold text-gray-800">{user?.bank_account_owner || user?.name || '-'}</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-500 text-sm mb-1">Bank Account Number</div>
                                            <div className="font-semibold text-gray-800">{user?.bank_account_number || '-'}</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-500 text-sm mb-1">Owner NRIC</div>
                                            <div className="font-semibold text-gray-800">{user?.nric || '-'}</div>
                                        </div>
                                    </div>
                                    <div className="mt-6">
                                        <button className="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                            Update Bank Profile
                                        </button>
                                    </div>
                                </div>
                            </div>
                        )}

                        {activeReferrerTab === 'commission' && (
                            <div className="space-y-4">
                                <h3 className="font-semibold text-gray-800">My Commission</h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white">
                                        <div className="text-sm opacity-90">Total Accumulated Commission</div>
                                        <div className="text-3xl font-bold mt-2">MYR 14,346.45</div>
                                    </div>
                                    <div className="bg-gradient-to-br from-teal-400 to-teal-500 rounded-xl p-6 text-gray-800">
                                        <div className="text-sm text-gray-700">Current Month Commission</div>
                                        <div className="text-3xl font-bold mt-2">MYR 1,098.96</div>
                                    </div>
                                </div>
                                
                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <h4 className="font-semibold text-gray-800">Monthly Commission History</h4>
                                        <a href="#" className="text-sm text-emerald-600 hover:text-emerald-700">Click to view more</a>
                                    </div>
                                    <div className="space-y-3 max-h-80 overflow-y-auto">
                                        {commissionData.map((item, index) => (
                                            <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <span className="font-medium text-gray-800">{item.month}</span>
                                                <div className="flex items-center gap-3">
                                                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                        item.status === 'Paid' 
                                                            ? 'bg-green-100 text-green-800' 
                                                            : 'bg-gray-100 text-gray-800'
                                                    }`}> 
                                                        {item.status}
                                                    </span>
                                                    <span className="font-semibold text-gray-800">MYR {item.amount}</span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                );

            default:
                return null;
        }
	};

    const handleViewClientDetails = async (client: any) => {
        try {
            setSelectedClient(client);
            setShowClientModal(true);
            const res = await apiService.getClientPayments(client.id);
            if (res.success && res.data) {
                setClientPayments(res.data || []);
            } else {
                setClientPayments([]);
            }
        } catch {
            setClientPayments([]);
        }
    };

    const handleDownloadClientCard = async (client: any) => {
        try {
            const blob = await apiService.downloadClientCard(client.id);
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `medical-card-${client.full_name.replace(/[^A-Za-z0-9]/g, '')}.svg`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        } catch (e) {
            alert('Failed to download card');
        }
	};

	return (
        <>
            <AnimatePresence>
                {showReceiptModal && selectedPayment && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                    >
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0, y: 20 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            exit={{ scale: 0.9, opacity: 0, y: 20 }}
                            transition={{ type: "spring", stiffness: 300, damping: 30 }}
                            className="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                        >
                            <div className="flex items-center justify-between p-6 border-b border-gray-200">
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span className="text-white font-bold text-lg">W</span>
                                    </div>
                                    <div>
                                        <div className="font-bold text-gray-800">WE KONGSI</div>
                                        <div className="text-sm text-gray-600">Kita Kongsi Sdn Bhd (1492373D)</div>
                                    </div>
                                </div>
					<button
                                    onClick={() => setShowReceiptModal(false)}
                                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
					>
                                    <X className="w-5 h-5 text-gray-600" />
					</button>
				</div>

                            <div className="p-6 space-y-6">
                                <h2 className="text-2xl font-bold text-center text-gray-800">OFFICIAL RECEIPT</h2>
                                
                                <div className="space-y-4">
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Date:</span>
                                        <span className="font-semibold">10/08/2025 03:00:52</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Payer Name:</span>
                                        <span className="font-semibold">NOR ZAKIAH BINTI WAN OMAR</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">The Sum of Ringgit (RM):</span>
                                        <span className="font-semibold">{selectedPayment.amount}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">In Payment Of:</span>
                                        <span className="font-semibold">Membership Fee</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Order Number:</span>
                                        <span className="font-semibold">pa_20250810_xfffxt</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Payment Date:</span>
                                        <span className="font-semibold">10/08/2025 03:00:52</span>
                                    </div>
                                </div>
                                
                                <div className="text-sm text-gray-500 text-center py-4 border-t border-gray-200">
                                    NOTE: This receipt is computer generated and no signature is required.
                                </div>
                                
                                <div className="text-xs text-gray-600 space-y-1">
                                    <p>Kita Kongsi Sdn Bhd [202201046676 (1492373-D)]</p>
                                    <p>C/O WeWork Level 18, Equatorial Plaza, Jalan Sultan Ismail, 50250 Kuala Lumpur, Malaysia.</p>
                                    <p>info@wekongsi.com</p>
                                    <p>+60 11-5671 3670</p>
                                </div>
                            </div>
                            
                            <div className="p-6 border-t border-gray-200">
                                <div className="flex gap-3">
                                    <button className="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                        <Download className="w-4 h-4" />
                                        Download PDF
                                    </button>
                                    <button className="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        <Printer className="w-4 h-4" />
                                        Print
                                    </button>
						</div>
					</div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <AnimatePresence>
                {showUpdateProfileModal && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                    >
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0, y: 20 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            exit={{ scale: 0.9, opacity: 0, y: 20 }}
                            transition={{ type: "spring", stiffness: 300, damping: 30 }}
                            className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                        >
                            <div className="flex items-center justify-between p-6 border-b border-gray-200">
                                <h2 className="text-xl font-bold text-gray-800">Update Profile</h2>
                                <button
                                    onClick={() => setShowUpdateProfileModal(false)}
                                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    <X className="w-5 h-5 text-gray-600" />
                                </button>
                            </div>
                            
                            <div className="p-6 space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">Name</label>
                                        <input
                                            type="text"
                                            value={profileForm.name}
                                            onChange={(e) => setProfileForm({...profileForm, name: e.target.value})}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input
                                            type="email"
                                            value={profileForm.email}
                                            onChange={(e) => setProfileForm({...profileForm, email: e.target.value})}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                                        <input
                                            type="text"
                                            value={profileForm.streetAddress}
                                            onChange={(e) => setProfileForm({...profileForm, streetAddress: e.target.value})}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                        />
                                    </div>
								<div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                                        <input
                                            type="text"
                                            value={profileForm.postalCode}
                                            onChange={(e) => setProfileForm({...profileForm, postalCode: e.target.value})}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                        />
								</div>
								<div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">State</label>
                                        <select
                                            value={profileForm.state}
                                            onChange={(e) => setProfileForm({...profileForm, state: e.target.value})}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                        >
                                            <option value="Selangor">Selangor</option>
                                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                                            <option value="Johor">Johor</option>
                                            <option value="Penang">Penang</option>
                                            <option value="Perak">Perak</option>
                                            <option value="Negeri Sembilan">Negeri Sembilan</option>
                                            <option value="Melaka">Melaka</option>
                                            <option value="Pahang">Pahang</option>
                                            <option value="Terengganu">Terengganu</option>
                                            <option value="Kelantan">Kelantan</option>
                                            <option value="Kedah">Kedah</option>
                                            <option value="Perlis">Perlis</option>
                                            <option value="Sabah">Sabah</option>
                                            <option value="Sarawak">Sarawak</option>
                                        </select>
								</div>
								<div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">City</label>
                                        <input
                                            type="text"
                                            value={profileForm.city}
                                            onChange={(e) => setProfileForm({...profileForm, city: e.target.value})}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-2">Account Password</label>
                                        <input
                                            type="password"
                                            value={profileForm.accountPassword}
                                            onChange={(e) => setProfileForm({...profileForm, accountPassword: e.target.value})}
                                            placeholder="Enter your account password"
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                        />
                                        <p className="text-sm text-gray-500 mt-1">Please enter your account password to update profile</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div className="p-6 border-t border-gray-200">
                                <div className="flex gap-3 justify-end">
                                    <button
                                        onClick={() => setShowUpdateProfileModal(false)}
                                        className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={handleSubmitUpdateProfile}
                                        className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                    >
                                        Update
                                    </button>
                                </div>
                            </div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <AnimatePresence>
                {showChangePhoneModal && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                    >
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0, y: 20 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            exit={{ scale: 0.9, opacity: 0, y: 20 }}
                            transition={{ type: "spring", stiffness: 300, damping: 30 }}
                            className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                        >
                            <div className="flex items-center justify-between p-6 border-b border-gray-200">
                                <div className="flex items-center gap-3">
                                    {phoneChangeStep !== 'initial' && (
                                        <button
                                            onClick={handlePhoneBack}
                                            className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                        >
                                            <ArrowLeft className="w-5 h-5 text-gray-600" />
                                        </button>
                                    )}
                                    <h2 className="text-xl font-bold text-gray-800">
                                        {phoneChangeStep === 'initial' && 'Change Phone Number'}
                                        {phoneChangeStep === 'tac-verification' && 'TAC Verification'}
                                        {phoneChangeStep === 'new-phone' && 'Enter New Phone Number'}
                                        {phoneChangeStep === 'new-tac-verification' && 'Verify New Phone Number'}
                                        {phoneChangeStep === 'success' && 'Phone Number Changed Successfully'}
                                    </h2>
                                </div>
                                <button
                                    onClick={() => {
                                        setShowChangePhoneModal(false);
                                        resetPhoneChange();
                                    }}
                                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    <X className="w-5 h-5 text-gray-600" />
                                </button>
                            </div>
                            
                            <div className="p-6">
                                {phoneChangeStep === 'initial' && (
                                    <div className="flex items-center gap-6">
                                        <div className="flex-1 flex justify-center">
                                            <div className="relative">
                                                <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <Shield className="w-12 h-12 text-emerald-600" />
                                                </div>
                                                <div className="absolute inset-0 w-24 h-24 bg-emerald-100 rounded-full opacity-20 animate-pulse"></div>
                                            </div>
                                        </div>
                                        <div className="flex-1 space-y-4">
                                            <h3 className="text-lg font-semibold text-gray-800">Change Phone Number</h3>
                                            <p className="text-gray-600">
                                                To change your phone number, first verify your current phone number. Click Continue to have the TAC code sent to your registered phone number.
                                            </p>
                                            <button
                                                onClick={handlePhoneContinue}
                                                className="w-full px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium"
                                            >
                                                Continue
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {phoneChangeStep === 'tac-verification' && (
                                    <div className="flex items-center gap-6">
                                        <div className="flex-1 flex justify-center">
                                            <div className="relative">
                                                <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <Shield className="w-12 h-12 text-emerald-600" />
                                                </div>
                                                <div className="absolute inset-0 w-24 h-24 bg-emerald-100 rounded-full opacity-20 animate-pulse"></div>
                                            </div>
                                        </div>
                                        <div className="flex-1 space-y-4">
                                            <h3 className="text-lg font-semibold text-gray-800">TAC Verification</h3>
                                            <p className="text-gray-600">
                                                Please enter the TAC sent to your registered phone to verify your phone number.
                                            </p>
                                            <input
                                                type="text"
                                                value={tacCode}
                                                onChange={(e) => setTacCode(e.target.value)}
                                                placeholder="TAC"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                            />
                                            <button
                                                onClick={handlePhoneContinue}
                                                className="w-full px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium"
                                            >
                                                Continue
                                            </button>
                                            <button className="w-full text-emerald-600 hover:text-emerald-700 text-sm">
                                                Resend TAC
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {phoneChangeStep === 'new-phone' && (
                                    <div className="flex items-center gap-6">
                                        <div className="flex-1 flex justify-center">
                                            <div className="relative">
                                                <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <Phone className="w-12 h-12 text-emerald-600" />
                                                </div>
                                                <div className="absolute inset-0 w-24 h-24 bg-emerald-100 rounded-full opacity-20 animate-pulse"></div>
                                            </div>
                                        </div>
                                        <div className="flex-1 space-y-4">
                                            <h3 className="text-lg font-semibold text-gray-800">Enter New Phone Number</h3>
                                            <p className="text-gray-600">
                                                Please enter your new phone number. A TAC code will be sent to verify the new number.
                                            </p>
                                            <input
                                                type="tel"
                                                value={newPhoneNumber}
                                                onChange={(e) => setNewPhoneNumber(e.target.value)}
                                                placeholder="+60XXXXXXXXX"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                            />
                                            <button
                                                onClick={handlePhoneContinue}
                                                className="w-full px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium"
                                            >
                                                Continue
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {phoneChangeStep === 'new-tac-verification' && (
                                    <div className="flex items-center gap-6">
                                        <div className="flex-1 flex justify-center">
                                            <div className="relative">
                                                <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <Shield className="w-12 h-12 text-emerald-600" />
                                                </div>
                                                <div className="absolute inset-0 w-24 h-24 bg-emerald-100 rounded-full opacity-20 animate-pulse"></div>
                                            </div>
                                        </div>
                                        <div className="flex-1 space-y-4">
                                            <h3 className="text-lg font-semibold text-gray-800">Verify New Phone Number</h3>
                                            <p className="text-gray-600">
                                                Please enter the TAC sent to your new phone number to complete the verification.
                                            </p>
                                            <input
                                                type="text"
                                                value={newTacCode}
                                                onChange={(e) => setNewTacCode(e.target.value)}
                                                placeholder="TAC"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                            />
                                            <button
                                                onClick={handlePhoneContinue}
                                                className="w-full px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium"
                                            >
                                                Continue
                                            </button>
                                            <button className="w-full text-emerald-600 hover:text-emerald-700 text-sm">
                                                Resend TAC
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {phoneChangeStep === 'success' && (
                                    <div className="flex items-center gap-6">
                                        <div className="flex-1 flex justify-center">
                                            <div className="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                                                <CheckCircle className="w-12 h-12 text-green-600" />
                                            </div>
                                        </div>
                                        <div className="flex-1 space-y-4">
                                            <h3 className="text-lg font-semibold text-gray-800">Phone Number Changed Successfully!</h3>
                                            <p className="text-gray-600">
                                                Your phone number has been updated successfully. You can now use your new phone number for future verifications.
                                            </p>
                                            <button
                                                onClick={() => {
                                                    setShowChangePhoneModal(false);
                                                    resetPhoneChange();
                                                }}
                                                className="w-full px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium"
                                            >
                                                Done
                                            </button>
                                        </div>
                                    </div>
                                )}

                                <div className="flex justify-center mt-8">
                                    <div className="flex gap-2">
                                        {['initial', 'tac-verification', 'new-phone', 'new-tac-verification', 'success'].map((step, index) => (
                                            <div
                                                key={step}
                                                className={`w-3 h-3 rounded-full ${
                                                    phoneChangeStep === step
                                                        ? 'bg-emerald-600'
                                                        : index < ['initial', 'tac-verification', 'new-phone', 'new-tac-verification', 'success'].indexOf(phoneChangeStep)
                                                        ? 'bg-emerald-600'
                                                        : 'bg-gray-300'
                                                }`}
                                            />
                                        ))}
								</div>
							</div>
						</div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <AnimatePresence>
                {showChangePasswordModal && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                    >
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0, y: 20 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            exit={{ scale: 0.9, opacity: 0, y: 20 }}
                            transition={{ type: "spring", stiffness: 300, damping: 30 }}
                            className="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                        >
                            <div className="flex items-center justify-between p-6 border-b border-gray-200">
                                <h2 className="text-xl font-bold text-gray-800">Change Password</h2>
                                <button
                                    onClick={() => setShowChangePasswordModal(false)}
                                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    <X className="w-5 h-5 text-gray-600" />
                                </button>
                            </div>
                            
                            <div className="p-6 space-y-4">
								<div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Old Password</label>
                                    <input
                                        type="password"
                                        value={oldPassword}
                                        onChange={(e) => setOldPassword(e.target.value)}
                                        placeholder="Old Password"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    />
								</div>
								<div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                    <input
                                        type="password"
                                        value={newPassword}
                                        onChange={(e) => setNewPassword(e.target.value)}
                                        placeholder="New Password"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    />
								</div>
								<div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                    <input
                                        type="password"
                                        value={confirmPassword}
                                        onChange={(e) => setConfirmPassword(e.target.value)}
                                        placeholder="Confirm New Password"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    />
                                </div>
                                {passwordError && (
                                    <div className="bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p className="text-red-800 text-sm">{passwordError}</p>
                                    </div>
                                )}
                            </div>
                            
                            <div className="p-6 border-t border-gray-200">
                                <div className="flex gap-3 justify-end">
                                    <button
                                        onClick={() => setShowChangePasswordModal(false)}
                                        className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={handlePasswordUpdate}
                                        className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                    >
                                        Update
                                    </button>
                                </div>
                            </div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <AnimatePresence>
                {showLogoutConfirmModal && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                    >
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0, y: 20 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            exit={{ scale: 0.9, opacity: 0, y: 20 }}
                            transition={{ type: "spring", stiffness: 300, damping: 30 }}
                            className="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                        >
                            <div className="flex items-center justify-between p-6 border-b border-gray-200">
                                <h2 className="text-xl font-bold text-gray-800">Confirm Logout</h2>
                                <button
                                    onClick={() => setShowLogoutConfirmModal(false)}
                                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    <X className="w-5 h-5 text-gray-600" />
                                </button>
                            </div>
                            
                            <div className="p-6 space-y-4">
                                <p className="text-gray-600">Are you sure you want to log out?</p>
                                <div className="flex gap-3 justify-end">
                                    <button
                                        onClick={() => setShowLogoutConfirmModal(false)}
                                        className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={handleLogout}
                                        className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                    >
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <MedicalInsuranceRegistrationForm
                isOpen={showMedicalInsuranceModal}
                onClose={() => setShowMedicalInsuranceModal(false)}
                onSuccess={handleMedicalInsuranceSuccess}
            />

            {/* Policy Details Modal */}
            <AnimatePresence>
                {showPolicyModal && selectedPolicy && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                    >
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0 }}
                            animate={{ scale: 1, opacity: 1 }}
                            exit={{ scale: 0.9, opacity: 0 }}
                            className="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden"
                        >
                            {/* Modal Header */}
                            <div className={`bg-gradient-to-r ${selectedPolicy.color} p-6 text-white`}>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-4">
                                        <Shield className="w-8 h-8" />
                                        <div>
                                            <h2 className="text-2xl font-bold">{selectedPolicy.name}</h2>
                                            <p className="text-white/90">{selectedPolicy.type}</p>
                                        </div>
                                    </div>
                                    <button
                                        onClick={() => setShowPolicyModal(false)}
                                        className="text-white/80 hover:text-white transition-colors"
                                    >
                                        <X className="w-6 h-6" />
                                    </button>
                                </div>
                            </div>

                            {/* Modal Content */}
                            <div className="p-6 max-h-[70vh] overflow-y-auto">
                                {/* Key Details */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <div className="space-y-4">
                                        <div className="bg-gray-50 rounded-lg p-4">
                                            <h3 className="font-semibold text-gray-800 mb-3">Coverage Details</h3>
                                            <div className="space-y-2">
                                                <div className="flex justify-between">
                                                    <span className="text-gray-600">Annual Limit</span>
                                                    <span className="font-medium">{selectedPolicy.details.annualLimit}</span>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span className="text-gray-600">Room & Board</span>
                                                    <span className="font-medium">{selectedPolicy.details.roomBoard}</span>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span className="text-gray-600">Panel Hospitals</span>
                                                    <span className="font-medium">{selectedPolicy.details.panelHospitals}</span>
                                                </div>
                                                {selectedPolicy.details.panelClinics && (
                                                    <div className="flex justify-between">
                                                        <span className="text-gray-600">Panel Clinics</span>
                                                        <span className="font-medium">{selectedPolicy.details.panelClinics}</span>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        <div className="bg-gray-50 rounded-lg p-4">
                                            <h3 className="font-semibold text-gray-800 mb-3">Plan Information</h3>
                                            <div className="space-y-2">
                                                <div className="flex justify-between">
                                                    <span className="text-gray-600">TPA</span>
                                                    <span className="font-medium text-sm">{selectedPolicy.details.tpa}</span>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span className="text-gray-600">Age Eligibility</span>
                                                    <span className="font-medium text-sm">{selectedPolicy.details.ageEligibility}</span>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span className="text-gray-600">Waiting Period</span>
                                                    <span className="font-medium text-sm">{selectedPolicy.details.waitingPeriod}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Pricing (if available) */}
                                {selectedPolicy.details.pricing && (
                                    <div className="mb-8">
                                        <h3 className="font-semibold text-gray-800 mb-4">Pricing Options</h3>
                                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            {Object.entries(selectedPolicy.details.pricing).map(([frequency, price]) => (
                                                <div key={frequency} className="bg-gray-50 rounded-lg p-4 text-center">
                                                    <div className="text-sm text-gray-600 capitalize mb-1">{frequency}</div>
                                                    <div className="font-semibold text-gray-800">{String(price)}</div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Benefits */}
                                <div className="mb-8">
                                    <h3 className="font-semibold text-gray-800 mb-4">Benefits & Coverage</h3>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        {selectedPolicy.details.benefits.map((benefit: string, index: number) => (
                                            <div key={index} className="flex items-start gap-2">
                                                <CheckCircle className="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                                                <span className="text-sm text-gray-700">{benefit}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {/* Modal Footer */}
                            <div className="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                                <button
                                    onClick={() => setShowPolicyModal(false)}
                                    className="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
                                >
                                    Close
                                </button>
                            </div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <PageTransition>
                <div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6 bg-gradient-to-br from-blue-50/30 via-white to-emerald-50/30">
                    <div className="w-full max-w-6xl xl:max-w-7xl green-gradient-border p-3 sm:p-4 md:p-6 lg:p-8 xl:p-10">
                        {/* Enhanced Logout Button - Moved Higher and Better UI/UX */}
                        <motion.div 
                            initial={{ opacity: 0, y: -20, scale: 0.9 }}
                            animate={{ opacity: 1, y: 0, scale: 1 }}
                            transition={{ duration: 0.5, delay: 0.2 }}
                            className="absolute top-2 sm:top-3 md:top-4 right-2 sm:right-3 md:right-6 z-[100]"
                        >
                            <button
                                onClick={() => setShowLogoutConfirmModal(true)}
                                aria-label="Logout"
                                title="Logout"
                                className="grid place-content-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-red-500 hover:bg-red-600 text-white border border-red-400 transition-colors duration-200 shadow-md hover:shadow-lg"
                            >
                                <LogOut size={16} />
                            </button>
                        </motion.div>

                        <div className="grid grid-cols-1 md:grid-cols-[280px_1fr] lg:grid-cols-[300px_1fr] xl:grid-cols-[320px_1fr] gap-4 sm:gap-6 md:gap-8">
                            <StaggeredContainer className="space-y-4">
                                <StaggeredItem>
                                    <div className="space-y-3">
                                        <div className="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full text-white grid place-content-center text-xl font-bold shadow-lg">
                                            N
								</div>
								<div>
                                            <div className="font-bold text-lg text-gray-800">{user?.name || '-'}</div>
                                            <div className="text-gray-500 text-sm">{user?.phone_number || '-'}</div>
								</div>
							</div>
                                </StaggeredItem>
                                
                                <StaggeredItem>
                                    <nav className="space-y-2">
                                        {navigationItems.map((item) => (
                                            <div key={item.id}>
                                                <button
                                                    onClick={() => setActiveTab(item.id as TabType)}
                                                    className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 ${
                                                        item.active
                                                            ? 'bg-emerald-600 text-white shadow-md'
                                                            : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'
                                                    }`}
                                                >
                                                    <item.icon size={18} />
                                                    <span>{item.label}</span>
                                                </button>
						</div>
                                        ))}
                                    </nav>
                                </StaggeredItem>
                            </StaggeredContainer>

                            <FadeIn delay={0.2}>
                                <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6 md:p-8">
                                    <AnimatePresence mode="wait">
                                        <motion.div
                                            key={activeTab}
                                            initial={{ opacity: 0, y: 20 }}
                                            animate={{ opacity: 1, y: 0 }}
                                            exit={{ opacity: 0, y: -20 }}
                                            transition={{ duration: 0.3 }}
                                        >
                                            {renderTabContent()}
                                        </motion.div>
                                    </AnimatePresence>
						</div>
                            </FadeIn>
					</div>
				</div>
			</div>
            </PageTransition>

            {/* Enhanced Client Details Modal */}
            <AnimatePresence>
                {showClientModal && selectedClient && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/60 backdrop-blur-md flex items-center justify-center p-4"
                        onClick={() => setShowClientModal(false)}
                    >
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0, y: 20 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            exit={{ scale: 0.9, opacity: 0, y: 20 }}
                            transition={{ type: "spring", stiffness: 300, damping: 30 }}
                            className="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden"
                            onClick={(e) => e.stopPropagation()}
                        >
                            {/* Enhanced Header */}
                            <div className="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white p-6">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-4">
                                        <div className="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-white font-bold text-2xl">
                                            {selectedClient.full_name?.charAt(0) || 'C'}
                                        </div>
                                        <div>
                                            <h2 className="text-2xl font-bold">{selectedClient.full_name}</h2>
                                            <p className="text-emerald-100 text-sm">Client ID: {selectedClient.id}</p>
                                        </div>
                                    </div>
                                    <button 
                                        onClick={() => setShowClientModal(false)} 
                                        className="p-3 hover:bg-white/20 rounded-xl transition-colors"
                                    >
                                        <X className="w-6 h-6" />
                                    </button>
                                </div>
                                
                                {/* Status Badge */}
                                <div className="mt-4">
                                    <span className={`inline-flex items-center px-4 py-2 rounded-full text-sm font-medium ${
                                        selectedClient.status === 'active' 
                                            ? 'bg-green-500 text-white' 
                                            : 'bg-gray-500 text-white'
                                    }`}>
                                        <div className={`w-2 h-2 rounded-full mr-2 ${
                                            selectedClient.status === 'active' ? 'bg-white' : 'bg-gray-300'
                                        }`}></div>
                                        {selectedClient.status?.toUpperCase()}
                                    </span>
                                </div>
                            </div>

                            {/* Enhanced Content */}
                            <div className="p-6 overflow-y-auto max-h-[calc(95vh-200px)]">
                                {/* Personal Information Section */}
                                <div className="mb-8">
                                    <div className="flex items-center gap-2 mb-4">
                                        <div className="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <UserIcon className="w-4 h-4 text-blue-600" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-gray-800">Personal Information</h3>
                                    </div>
                                    
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        <div className="bg-gray-50 rounded-xl p-4">
                                            <div className="text-xs text-gray-500 mb-1">Full Name</div>
                                            <div className="font-semibold text-gray-800 text-sm">{selectedClient.full_name}</div>
                                        </div>
                                        <div className="bg-gray-50 rounded-xl p-4">
                                            <div className="text-xs text-gray-500 mb-1">NRIC Number</div>
                                            <div className="font-semibold text-gray-800 text-sm font-mono">{selectedClient.nric}</div>
                                        </div>
                                        <div className="bg-gray-50 rounded-xl p-4">
                                            <div className="text-xs text-gray-500 mb-1">Phone Number</div>
                                            <div className="font-semibold text-gray-800 text-sm">{selectedClient.phone_number}</div>
                                        </div>
                                        <div className="bg-gray-50 rounded-xl p-4">
                                            <div className="text-xs text-gray-500 mb-1">Email Address</div>
                                            <div className="font-semibold text-gray-800 text-sm">{selectedClient.email || 'Not provided'}</div>
                                        </div>
                                        <div className="bg-gray-50 rounded-xl p-4">
                                            <div className="text-xs text-gray-500 mb-1">Date of Birth</div>
                                            <div className="font-semibold text-gray-800 text-sm">{selectedClient.date_of_birth || 'Not provided'}</div>
                                        </div>
                                        <div className="bg-gray-50 rounded-xl p-4">
                                            <div className="text-xs text-gray-500 mb-1">Gender</div>
                                            <div className="font-semibold text-gray-800 text-sm capitalize">{selectedClient.gender || 'Not specified'}</div>
                                        </div>
                                    </div>
                                </div>

                                {/* Insurance Information Section */}
                                <div className="mb-8">
                                    <div className="flex items-center gap-2 mb-4">
                                        <div className="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                            <Shield className="w-4 h-4 text-emerald-600" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-gray-800">Insurance Information</h3>
                                    </div>
                                    
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div className="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                                            <div className="text-xs text-emerald-600 mb-1">Insurance Plan</div>
                                            <div className="font-bold text-emerald-800 text-lg">{selectedClient.plan_name}</div>
                                            <div className="text-sm text-emerald-700 mt-1">Payment Mode: {selectedClient.payment_mode}</div>
                                        </div>
                                        <div className="bg-blue-50 rounded-xl p-4 border border-blue-200">
                                            <div className="text-xs text-blue-600 mb-1">Medical Card Type</div>
                                            <div className="font-bold text-blue-800 text-lg">{selectedClient.medical_card_type}</div>
                                            <div className="text-sm text-blue-700 mt-1">Card Status: Active</div>
                                        </div>
                                    </div>
                                </div>

                                {/* Payment History Section */}
                                <div className="mb-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <div className="flex items-center gap-2">
                                            <div className="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                                <DollarSign className="w-4 h-4 text-green-600" />
                                            </div>
                                            <h3 className="text-lg font-semibold text-gray-800">Payment History</h3>
                                        </div>
                                        <div className="text-sm text-gray-500">
                                            {clientPayments.length} payment{clientPayments.length !== 1 ? 's' : ''}
                                        </div>
                                    </div>
                                    
                                    {clientPayments.length === 0 ? (
                                        <div className="bg-gray-50 rounded-xl p-8 text-center">
                                            <DollarSign className="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                            <h4 className="text-lg font-semibold text-gray-600 mb-2">No Payments Found</h4>
                                            <p className="text-sm text-gray-500">This client hasn't made any payments yet.</p>
                                        </div>
                                    ) : (
                                        <div className="space-y-3">
                                            {clientPayments.map((payment, index) => (
                                                <div key={index} className="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                                    <div className="flex items-center justify-between">
                                                        <div className="flex-1">
                                                            <div className="flex items-center gap-3 mb-2">
                                                                <div className={`w-3 h-3 rounded-full ${
                                                                    payment.status === 'completed' ? 'bg-green-500' : 
                                                                    payment.status === 'pending' ? 'bg-yellow-500' : 'bg-red-500'
                                                                }`}></div>
                                                                <h4 className="font-semibold text-gray-800">{payment.description || 'Payment'}</h4>
                                                                <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                                    payment.status === 'completed' ? 'bg-green-100 text-green-800' :
                                                                    payment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
                                                                }`}>
                                                                    {payment.status?.toUpperCase()}
                                                                </span>
                                                            </div>
                                                            <div className="text-sm text-gray-600">
                                                                {new Date(payment.completed_at || payment.created_at).toLocaleDateString('en-MY', {
                                                                    year: 'numeric',
                                                                    month: 'long',
                                                                    day: 'numeric',
                                                                    hour: '2-digit',
                                                                    minute: '2-digit'
                                                                })}
                                                            </div>
                                                        </div>
                                                        <div className="text-right">
                                                            <div className="text-xl font-bold text-gray-800">RM {Number(payment.amount).toFixed(2)}</div>
                                                            <div className="text-xs text-gray-500">Amount</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>

                                {/* Action Buttons */}
                                <div className="flex gap-3 pt-4 border-t border-gray-200">
                                    <button 
                                        onClick={() => handleDownloadClientCard(selectedClient)}
                                        className="flex-1 bg-emerald-600 text-white py-3 px-4 rounded-xl hover:bg-emerald-700 transition-colors font-medium flex items-center justify-center gap-2"
                                    >
                                        <Download className="w-4 h-4" />
                                        Download Client Card
                                    </button>
                                    <button 
                                        onClick={() => setShowClientModal(false)}
                                        className="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
                                    >
                                        Close
                                    </button>
                                </div>
                            </div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>
        </>
	);
}
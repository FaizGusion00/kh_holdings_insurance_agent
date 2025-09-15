"use client";

import { useState, useEffect, useRef } from "react";
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
    Mail,
    Plus,
    XCircle,
    Wallet,
    Target,
    Users,
    Activity,
    Clock,
    BarChart3,
    CreditCard,
    Heart
} from "lucide-react";
import { PageTransition, FadeIn, StaggeredContainer, StaggeredItem } from "../(ui)/components/PageTransition";
import { motion, AnimatePresence } from "framer-motion";
import MedicalInsuranceRegistrationForm from "../(ui)/components/MedicalInsuranceRegistrationForm";
import { Modal } from "../(ui)/components/Modal";

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
    const [showViewUserModal, setShowViewUserModal] = useState(false);
    const [selectedUser, setSelectedUser] = useState<any>(null);
    const [selectedUserDownlines, setSelectedUserDownlines] = useState<any[]>([]);
    const [selectedUserCommissionSummary, setSelectedUserCommissionSummary] = useState<any>(null);
    const [selectedUserLevelFilter, setSelectedUserLevelFilter] = useState<number | undefined>(undefined);
    const [selectedUserDownlinesPage, setSelectedUserDownlinesPage] = useState<number>(1);
    const [selectedUserByLevelCounts, setSelectedUserByLevelCounts] = useState<Record<string, number> | null>(null);
    const [isSelectedUserLoading, setIsSelectedUserLoading] = useState<boolean>(false);
    
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
    
    // Referrer tab states
    const [referralData, setReferralData] = useState<any>(null);
    const [directReferrals, setDirectReferrals] = useState<any[]>([]);
    const [downlines, setDownlines] = useState<any[]>([]);
    const [downlineStats, setDownlineStats] = useState<any>(null);
    const [commissionSummary, setCommissionSummary] = useState<any>(null);
    const [commissionHistoryData, setCommissionHistoryData] = useState<any[]>([]);
    const [isReferrerLoading, setIsReferrerLoading] = useState(false);
    const [referralSearch, setReferralSearch] = useState<string>("");
    const [referralStatusFilter, setReferralStatusFilter] = useState<string>("all");
    const [referralStatusOpen, setReferralStatusOpen] = useState<boolean>(false);
    const statusDropdownRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        function onDocClick(e: MouseEvent) {
            if (!referralStatusOpen) return;
            const target = e.target as Node;
            if (statusDropdownRef.current && !statusDropdownRef.current.contains(target)) {
                setReferralStatusOpen(false);
            }
        }
        document.addEventListener('mousedown', onDocClick);
        return () => document.removeEventListener('mousedown', onDocClick);
    }, [referralStatusOpen]);
    const [showBankInfoModal, setShowBankInfoModal] = useState(false);
    const [bankInfoForm, setBankInfoForm] = useState({
        bank_name: '',
        bank_account_number: '',
        bank_account_owner: '',
        current_password: ''
    });

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

    // Load referrer data
    const loadReferrerData = async () => {
        setIsReferrerLoading(true);
        try {
            const [referralsResponse, downlinesResponse, commissionSummaryResponse, commissionHistoryResponse] = await Promise.all([
                apiService.getReferrals(),
                apiService.getDownlines(2),
                apiService.getCommissionSummary(),
                apiService.getCommissionHistory()
            ]);
            
            if (referralsResponse.success && referralsResponse.data) {
                // Backend returns aggregated referral info at root, not under `referral`
                const d: any = referralsResponse.data as any;
                setReferralData(d);
                setDirectReferrals(d.direct_referrals || []);
                setDownlineStats(
                    d.downline_stats || {
                        direct_referrals_count: d.direct_referrals_count,
                        total_downlines_count: d.total_downlines_count,
                    }
                );
            }
            
            if (downlinesResponse.success && downlinesResponse.data) {
                setDownlines(downlinesResponse.data.data || []);
            }
            
            if (commissionSummaryResponse.success && commissionSummaryResponse.data) {
                setCommissionSummary(commissionSummaryResponse.data);
            }
            
            if (commissionHistoryResponse.success && commissionHistoryResponse.data) {
                setCommissionHistoryData(commissionHistoryResponse.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load referrer data:', error);
        } finally {
            setIsReferrerLoading(false);
        }
    };

    // Load payments and mandates on enter relevant tabs
    useEffect(() => {
        async function loadData() {
            setError("");
            if (!user) return;
            if (activeTab === 'overview') {
                loadOverviewData();
            } else if (activeTab === 'referrer') {
                loadReferrerData();
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

    // Copy referral link to clipboard
    const copyReferralLink = async () => {
        const base = process.env.NODE_ENV === 'production' ? 'https://wekongsi.com' : 'http://localhost:3000';
        const referralLink = `${base}/register-external?agent_code=${user?.agent_code || ''}`;
        try {
            await navigator.clipboard.writeText(referralLink);
            alert('Referral link copied to clipboard!');
        } catch (err) {
            console.error('Failed to copy referral link:', err);
            alert('Failed to copy referral link');
        }
    };

    // Handle bank info update
    const handleBankInfoUpdate = async () => {
        try {
            const response = await apiService.updateBankInfo(bankInfoForm);
            if (response.success) {
                alert('Bank information updated successfully!');
                setShowBankInfoModal(false);
                // Update user data
                if (user) {
                    updateUser({
                        ...user,
                        bank_name: bankInfoForm.bank_name,
                        bank_account_number: bankInfoForm.bank_account_number,
                        bank_account_owner: bankInfoForm.bank_account_owner
                    });
                }
            } else {
                alert('Failed to update bank information: ' + response.message);
            }
        } catch (error) {
            console.error('Error updating bank info:', error);
            alert('Failed to update bank information');
        }
    };

    // Initialize bank info form when modal opens
    const openBankInfoModal = () => {
        setBankInfoForm({
            bank_name: user?.bank_name || '',
            bank_account_number: user?.bank_account_number || '',
            bank_account_owner: user?.bank_account_owner || user?.name || '',
            current_password: ''
        });
        setShowBankInfoModal(true);
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
                                    Change Email
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
                                        planName: 'MediPlan Coop',
                                        description: 'Comprehensive medical insurance coverage with extensive benefits',
                                        type: 'Medical Cooperative Plan',
                                        premium: 'RM1,080/year or RM90/month',
                                        coveragePeriod: '1 Year',
                                        status: 'Active',
                                        gradient: 'from-blue-600 to-cyan-600',
                                            benefits: [
                                            { name: 'Room & Board', amount: 'RM250' },
                                            { name: 'Annual Limit', amount: 'RM1,000,000' },
                                            { name: 'Government Hospital Allowance', amount: 'RM100/day' }
                                        ],
                                        detailedBenefits: [
                                            { name: 'Room & Board', amount: '250' },
                                            { name: 'Ambulance Fees', amount: 'Included' },
                                            { name: 'Intensive Care Unit', amount: 'Included' },
                                            { name: 'Hospital Supplies & Services', amount: 'Included' },
                                            { name: 'Surgical Fees', amount: 'Included' },
                                            { name: 'Operating Theater Fees', amount: 'Included' },
                                            { name: 'Anesthetist Fees', amount: 'Included' },
                                            { name: 'In-hospital Doctor Visit', amount: 'Included' },
                                            { name: 'Day Care & Day Surgery', amount: 'Included' },
                                            { name: 'Second Surgical / Treatment Opinion', amount: 'Included' },
                                            { name: 'Emergency Accidental Dental Treatment', amount: 'Included' },
                                            { name: 'Covid Test for Admission Purpose', amount: 'Included' },
                                            { name: 'Daily Cash Allowance in Government Hospital', amount: '100' },
                                            { name: 'Pre-Hospital Diagnostic Test & Consultation', amount: '5,000' },
                                            { name: 'Accidental Injury Surgery / Treatment', amount: '10,000' },
                                            { name: 'Bereavement', amount: '10,000' },
                                            { name: 'Out-patient Cancer Treatment', amount: '100,000' },
                                            { name: 'Conditional Outpatient Benefits', amount: 'As Charged' }
                                        ],
                                        terms: [
                                            'Open to healthy Malaysian citizens with no pre-existing medical conditions',
                                            'Age eligibility: 30 days to 45 years old for enrollment, with renewal allowed up to 100 years old',
                                            'Contribution rates are fixed regardless of gender, age, or occupation',
                                            'Hospital admission must follow the prescribed procedures',
                                            '90 days waiting period for general illnesses',
                                            '180 days waiting period for specific illnesses',
                                            'Third Party Administrator - eMAS (Eximius Medical Administration Solutions)',
                                            '250 Panel Hospital, 4,000 Panel Clinics'
                                        ]
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
                                        planName: 'Senior Care Plan Gold 270',
                                        description: 'Comprehensive senior care insurance with extensive medical coverage',
                                        type: 'Senior Care Plan',
                                        premium: 'RM150/month + RM150 Commitment',
                                        coveragePeriod: '1 Year',
                                        status: 'Active',
                                        gradient: 'from-yellow-600 to-orange-600',
                                            benefits: [
                                            { name: 'Room & Board', amount: 'RM270/day' },
                                            { name: 'Annual Limit', amount: 'RM75,000' },
                                            { name: 'Government Hospital Allowance', amount: 'RM100/day' }
                                        ],
                                        detailedBenefits: [
                                            { name: 'Hospital Room & Board', amount: '270' },
                                            { name: 'Intensive Care Unit', amount: 'Full Reimbursement' },
                                            { name: 'Hospital Supplies and Services', amount: 'Included' },
                                            { name: 'Surgeon Fee', amount: 'Included' },
                                            { name: 'Anaesthetist Fee', amount: 'Included' },
                                            { name: 'Operating Theatre Charges', amount: 'Included' },
                                            { name: 'Daily in-Hospital Physician Visit', amount: 'Included' },
                                            { name: 'Pre-Hospital Diagnostic Tests', amount: 'Included' },
                                            { name: 'Pre-Hospitalization Specialist Consultation', amount: 'Included' },
                                            { name: 'Second Surgical Opinion', amount: 'Included' },
                                            { name: 'Post-Hospitalization Treatment', amount: 'Included' },
                                            { name: 'Emergency Accidental Outpatient Treatment', amount: 'Included' },
                                            { name: 'Outpatient Cancer Treatment', amount: 'Included' },
                                            { name: 'Outpatient Kidney Dialysis Treatment', amount: 'Included' },
                                            { name: 'Daycare Procedure', amount: 'Included' },
                                            { name: 'Ambulance Charges', amount: 'Included' },
                                            { name: 'Government Hospital Daily Cash Allowance', amount: '100' },
                                            { name: 'Medical Report Fee Reimbursement', amount: '80' },
                                            { name: 'Funeral Expenses - Accidental', amount: '10,000' }
                                        ],
                                        terms: [
                                            'Minimum entry age is 46 years, and maximum entry age is 65 years',
                                            'Renewals are allowed up to 70 years old',
                                            'Healthy Malaysian Citizens',
                                            'Payment modes include monthly, quarterly, semi-annual, and annual options',
                                            'Payment methods available: debit/credit card, FPX Online, BNPL, or e-Wallet',
                                            'Panel Hospital - MiCare: 148 Panel Hospital Nationwide',
                                            'Contributions as low as RM5.00 Per Day'
                                        ]
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
                                        planName: 'Senior Care Plan Diamond 370',
                                        description: 'Premium senior care insurance with enhanced medical coverage',
                                        type: 'Premium Senior Care',
                                        premium: 'RM210/month + RM210 Commitment',
                                        coveragePeriod: '1 Year',
                                        status: 'Active',
                                        gradient: 'from-purple-600 to-pink-600',
                                            benefits: [
                                            { name: 'Room & Board', amount: 'RM370/day' },
                                            { name: 'Annual Limit', amount: 'RM100,000' },
                                            { name: 'Government Hospital Allowance', amount: 'RM200/day' }
                                        ],
                                        detailedBenefits: [
                                            { name: 'Hospital Room & Board', amount: '370' },
                                            { name: 'Intensive Care Unit', amount: 'Full Reimbursement' },
                                            { name: 'Hospital Supplies and Services', amount: 'Included' },
                                            { name: 'Surgeon Fee', amount: 'Included' },
                                            { name: 'Anaesthetist Fee', amount: 'Included' },
                                            { name: 'Operating Theatre Charges', amount: 'Included' },
                                            { name: 'Daily in-Hospital Physician Visit', amount: 'Included' },
                                            { name: 'Pre-Hospital Diagnostic Tests', amount: 'Included' },
                                            { name: 'Pre-Hospitalization Specialist Consultation', amount: 'Included' },
                                            { name: 'Second Surgical Opinion', amount: 'Included' },
                                            { name: 'Post-Hospitalization Treatment', amount: 'Included' },
                                            { name: 'Emergency Accidental Outpatient Treatment', amount: 'Included' },
                                            { name: 'Outpatient Cancer Treatment', amount: 'Included' },
                                            { name: 'Outpatient Kidney Dialysis Treatment', amount: 'Included' },
                                            { name: 'Daycare Procedure', amount: 'Included' },
                                            { name: 'Ambulance Charges', amount: 'Included' },
                                            { name: 'Government Hospital Daily Cash Allowance', amount: '200' },
                                            { name: 'Medical Report Fee Reimbursement', amount: '80' },
                                            { name: 'Funeral Expenses - Accidental', amount: '10,000' }
                                        ],
                                        terms: [
                                            'Minimum entry age is 46 years, and maximum entry age is 65 years',
                                            'Renewals are allowed up to 70 years old',
                                            'Healthy Malaysian Citizens',
                                            'Payment modes include monthly, quarterly, semi-annual, and annual options',
                                            'Payment methods available: debit/credit card, FPX Online, BNPL, or e-Wallet',
                                            'Panel Hospital - MiCare: 148 Panel Hospital Nationwide',
                                            'Contributions as low as RM7.00 Per Day'
                                        ]
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
                                {isReferrerLoading ? (
                                    <div className="flex items-center justify-center py-12">
                                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
                                        <span className="ml-2 text-gray-600">Loading referral data...</span>
                                    </div>
                                ) : (
                                    <>
                                        {/* Account Overview */}
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div className="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white">
                                                <div className="text-sm opacity-90">Direct Referral</div>
                                                <div className="text-3xl font-bold mt-2">
                                                    {directReferrals.length}
                                        </div>
                                        </div>
                                            <div className="bg-white border border-gray-200 rounded-xl p-6">
                                                <div className="text-sm text-gray-500">Total Commission</div>
                                                <div className="text-3xl font-bold mt-2 text-gray-800">
                                                    RM {user?.total_commission_earned || '0.00'}
                                        </div>
                                    </div>
                                </div>

                                        {/* Referrals Breakdown */}
                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <div className="flex items-center justify-between mb-4">
                                                <h3 className="font-semibold text-gray-800">Referrals Breakdown</h3>
                                                <div className="flex items-center gap-2 relative" ref={statusDropdownRef}>
                                                    <input
                                                        type="text"
                                                        value={referralSearch}
                                                        onChange={(e) => setReferralSearch(e.target.value)}
                                                        placeholder="Search name, phone or code"
                                                        className="px-3 py-2 border border-gray-300 rounded-lg text-sm w-64"
                                                    />
                                                    <button
                                                        onClick={() => setReferralStatusOpen(v => !v)}
                                                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 flex items-center gap-1"
                                                    >
                                                        <span className="capitalize">{referralStatusFilter === 'all' ? 'All Status' : referralStatusFilter}</span>
                                                        <ChevronDown className={`w-4 h-4 transition-transform ${referralStatusOpen ? 'rotate-180' : ''}`} />
                                                    </button>
                                                    {referralStatusOpen && (
                                                        <div className="absolute right-0 top-full mt-2 w-40 bg-white border border-gray-200 rounded-md shadow-lg z-10">
                                                            {['all','active','pending','suspended','terminated'].map((s) => (
                                                                <div
                                                                    key={s}
                                                                    onClick={() => { setReferralStatusFilter(s); setReferralStatusOpen(false); }}
                                                                    className={`px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 capitalize ${referralStatusFilter === s ? 'text-emerald-700 font-semibold' : 'text-gray-700'}`}
                                                                >
                                                                    {s === 'all' ? 'All Status' : s}
                                                                </div>
                                                            ))}
                                                        </div>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Status Legend */}
                                            <div className="flex items-center gap-6 mb-4 text-sm">
                                                <div className="flex items-center gap-1">
                                                    <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                                    <span className="text-gray-600">Active</span>
                                                </div>
                                                <div className="flex items-center gap-1">
                                                    <div className="w-2 h-2 bg-orange-500 rounded-full"></div>
                                                    <span className="text-gray-600">Pending Payment, Probation</span>
                                                </div>
                                                <div className="flex items-center gap-1">
                                                    <div className="w-2 h-2 bg-red-500 rounded-full"></div>
                                                    <span className="text-gray-600">Suspended, Terminated, Probation Rejected</span>
                                    </div>
                                </div>

                                            {/* Level 1 Referrals */}
                                <div className="space-y-4">
                                                <div className="border border-gray-200 rounded-lg p-4">
                                                    <div className="flex items-center justify-between mb-3">
                                                        <h4 className="font-medium text-gray-800">Level 2</h4>
                                                        <div className="flex items-center gap-4 text-sm">
                                                            <div className="flex items-center gap-1">
                                                                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                                                <span className="text-gray-600">{directReferrals.filter(r => r.status === 'active').length}</span>
                                                </div>
                                                            <div className="flex items-center gap-1">
                                                                <div className="w-2 h-2 bg-orange-500 rounded-full"></div>
                                                                <span className="text-gray-600">{directReferrals.filter(r => r.status === 'pending').length}</span>
                                            </div>
                                                            <div className="flex items-center gap-1">
                                                                <div className="w-2 h-2 bg-red-500 rounded-full"></div>
                                                                <span className="text-gray-600">{directReferrals.filter(r => r.status === 'suspended' || r.status === 'terminated').length}</span>
                                        </div>
                                            </div>
                                        </div>

                                                    {/* Direct Referrals List */}
                                                    <div className="space-y-3 max-h-80 overflow-y-auto">
                                                        {directReferrals
                                                            .filter(r => referralStatusFilter === 'all' ? true : r.status === referralStatusFilter)
                                                            .filter(r => {
                                                                const q = referralSearch.trim().toLowerCase();
                                                                if (!q) return true;
                                                                const name = (r.agent?.name || '').toLowerCase();
                                                                const phone = (r.agent?.phone_number || '').toLowerCase();
                                                                const code = (r.agent?.agent_code || r.agent_code || '').toLowerCase();
                                                                return name.includes(q) || phone.includes(q) || code.includes(q);
                                                            }).length > 0 ? (
                                                            directReferrals
                                                                .filter(r => referralStatusFilter === 'all' ? true : r.status === referralStatusFilter)
                                                                .filter(r => {
                                                                    const q = referralSearch.trim().toLowerCase();
                                                                    if (!q) return true;
                                                                    const name = (r.agent?.name || '').toLowerCase();
                                                                    const phone = (r.agent?.phone_number || '').toLowerCase();
                                                                    const code = (r.agent?.agent_code || r.agent_code || '').toLowerCase();
                                                                    return name.includes(q) || phone.includes(q) || code.includes(q);
                                                                })
                                                                .map((referral, index) => (
                                                                    <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                                        <div className="flex items-center gap-3">
                                                                            <div className="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                                                                                <UserIcon className="w-4 h-4 text-emerald-600" />
                                                                            </div>
                                                                            <div>
                                                                                <div className="font-medium text-gray-800">
                                                                                    {referral.agent?.name || 'Unknown'}
                                                                                </div>
                                                                                <div className="text-sm text-gray-500">
                                                                                    {referral.agent?.phone_number || 'N/A'}
                                                                                </div>
                                                                                <div className="text-xs text-gray-400">
                                                                                    {new Date(referral.created_at).toLocaleDateString('en-US', {
                                                                                        year: 'numeric',
                                                                                        month: '2-digit',
                                                                                        day: '2-digit',
                                                                                        hour: '2-digit',
                                                                                        minute: '2-digit'
                                                                                    })}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div className="flex items-center gap-2">
                                                                            <div className="flex items-center gap-1">
                                                                                {referral.status === 'active' && <div className="w-2 h-2 bg-green-500 rounded-full"></div>}
                                                                                {referral.status === 'pending' && <div className="w-2 h-2 bg-orange-500 rounded-full"></div>}
                                                                                {(referral.status === 'suspended' || referral.status === 'terminated') && <div className="w-2 h-2 bg-red-500 rounded-full"></div>}
                                                                            </div>
                                                                            <button 
                                                                                onClick={() => handleViewUser(referral)}
                                                                                className="px-3 py-1 bg-emerald-600 text-white text-sm rounded hover:bg-emerald-700"
                                                                            >
                                                                                View User
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                ))
                                                        ) : (
                                                            <div className="text-center py-8 text-gray-500">
                                                                No direct referrals found
                                                    </div>
                                                        )}
                                            </div>
                                        </div>
                                                        </div>
                                                    </div>

                                        {/* My Referral Link */}
                                        <div className="bg-white border border-gray-200 rounded-lg p-6">
                                            <div className="flex items-center justify-between mb-4">
                                                <h3 className="font-semibold text-gray-800">My Referral Link</h3>
                                                <button
                                                    onClick={handleMedicalInsuranceRegistration}
                                                    className="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                                                >
                                                    <Plus className="w-4 h-4" />
                                                    Register
                                                </button>
                                                </div>
                                            <div className="space-y-3">
                                                <div className="flex gap-2">
                                                    <input
                                                        type="text"
                                                        value={`${process.env.NODE_ENV === 'production' ? 'https://wekongsi.com' : 'http://localhost:3000'}/register-external?agent_code=${user?.agent_code || ''}`}
                                                        readOnly
                                                        className="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm"
                                                    />
                                                    <button 
                                                        onClick={copyReferralLink}
                                                        className="px-4 py-2 bg-sky-600 text-white rounded-lg text-sm hover:bg-sky-700 transition-colors"
                                                    >
                                                        Copy Link
                                                    </button>
                                        </div>
                                    </div>
                                </div>

                                    </>
                                )}
                            </div>
                        )}

                        {activeReferrerTab === 'bank-info' && (
                            <div className="space-y-4">
                                <h3 className="font-semibold text-gray-800">Bank Profile</h3>
                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                        <div>
                                            <div className="text-gray-500 text-xs mb-1">Bank Name</div>
                                            <div className="font-semibold text-gray-900 text-sm sm:text-base">{user?.bank_name || '-'}</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-500 text-xs mb-1">Owner Name</div>
                                            <div className="font-semibold text-gray-900 text-sm sm:text-base">{user?.bank_account_owner || user?.name || '-'}</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-500 text-xs mb-1">Bank Account Number</div>
                                            <div className="font-semibold text-gray-900 text-sm sm:text-base">{user?.bank_account_number || '-'}</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-500 text-xs mb-1">Owner NRIC</div>
                                            <div className="font-semibold text-gray-900 text-sm sm:text-base">{user?.nric || '-'}</div>
                                        </div>
                                    </div>
                                    <div className="mt-6 flex items-center justify-between">
                                        <p className="text-xs text-gray-500">Keep your bank info updated to receive withdrawals without delay.</p>
                                        <button 
                                            onClick={openBankInfoModal}
                                            className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                                        >
                                            Update Bank Profile
                                        </button>
                                    </div>
                                </div>
                            </div>
                        )}

                        {activeReferrerTab === 'commission' && (
                            <div className="space-y-4">
                                {isReferrerLoading ? (
                                    <div className="flex items-center justify-center py-12">
                                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
                                        <span className="ml-2 text-gray-600">Loading commission data...</span>
                                    </div>
                                ) : (
                                    <>
                                <h3 className="font-semibold text-gray-800">My Commission</h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div className="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white">
                                        <div className="text-xs opacity-90">Total Accumulated</div>
                                        <div className="text-3xl font-bold mt-2">RM {commissionSummary?.total_commission || user?.total_commission_earned || '0.00'}</div>
                                    </div>
                                    <div className="bg-gradient-to-br from-teal-400 to-teal-500 rounded-xl p-6 text-gray-900">
                                        <div className="text-xs text-gray-800">Current Month</div>
                                        <div className="text-3xl font-bold mt-2">RM {commissionSummary?.monthly_commission || '0.00'}</div>
                                    </div>
                                    <div className="bg-white border border-gray-200 rounded-xl p-6">
                                        <div className="text-xs text-gray-500">Pending Commission</div>
                                        <div className="text-3xl font-bold mt-2 text-gray-800">RM {commissionSummary?.pending_commission || '0.00'}</div>
                                    </div>
                                </div>
                                
                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <h4 className="font-semibold text-gray-800">Monthly Commission History</h4>
                                        <span className="text-sm text-gray-500">{commissionHistoryData.length} records</span>
                                    </div>
                                    <div className="space-y-3 max-h-80 overflow-y-auto">
                                        {commissionHistoryData.length > 0 ? (
                                            commissionHistoryData.map((item, index) => (
                                                <div key={index} className="grid grid-cols-1 md:grid-cols-3 gap-3 p-3 bg-gray-50 rounded-lg">
                                                    <div>
                                                        <span className="font-medium text-gray-800">
                                                            {new Date(item.created_at).toLocaleDateString('en-US', { 
                                                                month: 'long', 
                                                                year: 'numeric' 
                                                            })}
                                                        </span>
                                                        <div className="text-xs text-gray-500">
                                                            {item.description || 'Commission Payment'}
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center md:justify-center">
                                                        <span className="font-semibold text-gray-800">RM {parseFloat(item.amount || 0).toFixed(2)}</span>
                                                    </div>
                                                    <div className="flex items-center md:justify-end">
                                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                            item.status === 'paid' || item.status === 'completed'
                                                                ? 'bg-green-100 text-green-800' 
                                                                : 'bg-gray-100 text-gray-800'
                                                        }`}>
                                                            {item.status || 'Pending'}
                                                        </span>
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="text-center py-8 text-gray-500">
                                                No commission history found
                                            </div>
                                        )}
                                    </div>
                                </div>
                                    </>
                                )}
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
            }
        } catch (error) {
            console.error('Error loading client payments:', error);
            setClientPayments([]);
        }
    };

    const handleDownloadClientCard = async (client: any) => {
        try {
            const logoResp = await fetch('/logo.png');
            const logoBlob = await logoResp.blob();
            const logoDataUrl = await new Promise<string>((resolve) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result as string);
                reader.readAsDataURL(logoBlob);
            });

            const fullName = client?.full_name || client?.name || 'Member';
            const memberId = client?.id || '';
            const plan = client?.plan_name || client?.plan || 'Medical Plan';
            const nric = client?.nric || '-';
            const issued = new Date().toLocaleDateString();

            const svg = `<?xml version="1.0" encoding="UTF-8"?><svg width="860" height="540" viewBox="0 0 860 540" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="g1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#065f46"/><stop offset="100%" stop-color="#10b981"/></linearGradient><filter id="shadow" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="12" stdDeviation="18" flood-color="#0f172a" flood-opacity="0.25"/></filter></defs><rect x="40" y="40" rx="28" ry="28" width="780" height="460" fill="url(#g1)" filter="url(#shadow)"/><rect x="58" y="58" rx="22" ry="22" width="744" height="424" fill="#ffffff" opacity="0.06"/><image href="${logoDataUrl}" x="72" y="70" width="120" height="120"/><text x="210" y="110" font-family="Inter, ui-sans-serif" font-size="26" fill="#ecfdf5" font-weight="600">WeKongsi Medical</text><text x="210" y="146" font-family="Inter, ui-sans-serif" font-size="16" fill="#d1fae5">Member Healthcare Access Card</text><rect x="72" y="212" width="716" height="2" fill="#34d399" opacity="0.7"/><text x="72" y="260" font-family="Inter, ui-sans-serif" font-size="18" fill="#a7f3d0">Full Name</text><text x="72" y="292" font-family="Inter, ui-sans-serif" font-size="28" fill="#ffffff" font-weight="700">${fullName}</text><text x="72" y="336" font-family="Inter, ui-sans-serif" font-size="18" fill="#a7f3d0">Plan</text><text x="72" y="366" font-family="Inter, ui-sans-serif" font-size="22" fill="#ecfeff" font-weight="600">${plan}</text><text x="460" y="260" font-family="Inter, ui-sans-serif" font-size="18" fill="#a7f3d0">NRIC</text><text x="460" y="292" font-family="Inter, ui-sans-serif" font-size="22" fill="#ffffff" font-weight="600">${nric}</text><text x="460" y="336" font-family="Inter, ui-sans-serif" font-size="18" fill="#a7f3d0">Member ID</text><text x="460" y="366" font-family="Inter, ui-sans-serif" font-size="22" fill="#ffffff" font-weight="600">#${memberId}</text><text x="72" y="420" font-family="Inter, ui-sans-serif" font-size="14" fill="#bbf7d0">Issued</text><text x="72" y="442" font-family="Inter, ui-sans-serif" font-size="16" fill="#ecfdf5">${issued}</text><text x="620" y="420" text-anchor="end" font-family="Inter, ui-sans-serif" font-size="12" fill="#a7f3d0">24/7 Support</text><text x="786" y="420" text-anchor="end" font-family="Inter, ui-sans-serif" font-size="12" fill="#a7f3d0">info@wekongsi.com</text></svg>`;

            const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `medical-card-${memberId || 'member'}.svg`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        } catch (e) {
            console.error('Download medical card failed', e);
        }
    };

    const handlePrintPolicy = (policy: any) => {
        // Implementation for printing policy
        console.log('Print policy:', policy);
        // You can implement actual print functionality here
        window.print();
    };

    const handleViewUser = async (referralOrUser: any) => {
        // Normalize to user object (direct referrals provide `agent`)
        const normalizedUser = referralOrUser?.agent ? referralOrUser.agent : referralOrUser;
        setSelectedUser(normalizedUser);
        setShowViewUserModal(true);
        setIsSelectedUserLoading(true);
        try {
            const downlinesRes = await apiService.getUserDownlines(normalizedUser.id, selectedUserLevelFilter, selectedUserDownlinesPage);
            const summaryRes = await apiService.getCommissionSummary();
            setSelectedUserDownlines(((downlinesRes as any)?.data?.data) || []);
            setSelectedUserByLevelCounts(((downlinesRes as any)?.data?.by_level_counts) || null);
            const s = (summaryRes as any)?.data || null;
            setSelectedUserCommissionSummary(s ? { total: s.total_commission, current_month: s.monthly_commission, pending: s.pending_commission } : null);
        } catch (e) {
            setSelectedUserDownlines([]);
            setSelectedUserByLevelCounts(null);
            setSelectedUserCommissionSummary(null);
        } finally {
            setIsSelectedUserLoading(false);
        }
    };

    const handleChangeSelectedUserLevel = async (level?: number) => {
        if (!selectedUser) return;
        setSelectedUserLevelFilter(level);
        setSelectedUserDownlinesPage(1);
        setIsSelectedUserLoading(true);
        try {
            const downlinesRes = await apiService.getUserDownlines(selectedUser.id, level, 1);
            setSelectedUserDownlines(((downlinesRes as any)?.data?.data) || []);
            setSelectedUserByLevelCounts(((downlinesRes as any)?.data?.by_level_counts) || null);
        } catch (e) {
            setSelectedUserDownlines([]);
            setSelectedUserByLevelCounts(null);
        } finally {
            setIsSelectedUserLoading(false);
        }
    };

    const loadClients = async () => {
        setIsLoading(true);
        try {
            const res = await apiService.getClients(1);
            if (res.success && res.data) {
                setClients((res.data as any).data || []);
            } else {
                setError(res.message || 'Failed to load clients');
            }
        } catch (e) {
            setError(e instanceof Error ? e.message : 'Failed to load clients');
        } finally {
            setIsLoading(false);
        }
	};

	return (
        <PageTransition>
            <div className="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4 lg:p-6 bg-gradient-to-br from-blue-50/30 via-white to-emerald-50/30">
                <div className="w-full max-w-6xl xl:max-w-7xl green-gradient-border p-3 sm:p-4 md:p-6 lg:p-8 xl:p-10">
                    {/* Enhanced Logout Button */}
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

                    {/* Main Content */}
                    <div className="space-y-6">
                        {/* Header */}
                        <FadeIn delay={0.1}>
                            <div className="text-center">
                                <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800 mb-2">
                                    Profile Dashboard
                                </h1>
                                            <p className="text-gray-600">
                                    Manage your account, view policies, and track your performance
                                </p>
                                    </div>
                        </FadeIn>

                        {/* Tab Navigation */}
                        <FadeIn delay={0.2}>
                            <div className="flex flex-wrap gap-2 justify-center">
                                {[
                                    { id: 'overview', label: 'Overview', icon: BarChart3 },
                                    { id: 'profile', label: 'Profile', icon: UserIcon },
                                    { id: 'policy-view', label: 'Policies', icon: FileText },
                                    { id: 'payment-history', label: 'Payments', icon: CreditCard },
                                    { id: 'medical-insurance', label: 'Medical Insurance', icon: Heart },
                                    { id: 'referrer', label: 'Referrer', icon: Users }
                                ].map((tab) => {
                                    const Icon = tab.icon;
                                    return (
					<button
                                            key={tab.id}
                                            onClick={() => setActiveTab(tab.id as TabType)}
                                            className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 ${
                                                activeTab === tab.id
                                                    ? 'bg-emerald-600 text-white shadow-md'
                                                    : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'
                                            }`}
                                        >
                                            <Icon className="w-4 h-4" />
                                            {tab.label}
					</button>
                                    );
                                })}
				</div>
                        </FadeIn>

                        {/* Tab Content */}
                        <FadeIn delay={0.3}>
                            <div className="bg-white rounded-xl border border-gray-200 shadow-sm">
                                {renderTabContent()}
                                    </div>
                        </FadeIn>
                                    </div>
                                    </div>
                                    </div>
                            
            {/* Modals */}
            <Modal open={showLogoutConfirmModal} onClose={() => setShowLogoutConfirmModal(false)} title="Confirm Logout">
                <div className="space-y-4">
                    <p className="text-gray-600">Are you sure you want to logout?</p>
                    <div className="flex justify-end gap-3">
                                <button
                                    onClick={() => setShowLogoutConfirmModal(false)}
                            className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
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
            </Modal>

            {/* Medical Insurance Registration Modal */}
            <Modal open={showMedicalInsuranceModal} onClose={() => setShowMedicalInsuranceModal(false)} title="Medical Insurance Registration" maxWidth="max-w-6xl">
            <MedicalInsuranceRegistrationForm
                isOpen={showMedicalInsuranceModal}
                onClose={() => setShowMedicalInsuranceModal(false)}
                    onSuccess={() => {
                        setShowMedicalInsuranceModal(false);
                        // Refresh clients list
                        loadClients();
                    }}
                />
            </Modal>

            {/* Bank Info Update Modal */}
            <Modal open={showBankInfoModal} onClose={() => setShowBankInfoModal(false)} title="Update Bank Information" maxWidth="max-w-2xl">
                <div className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                            <input
                                type="text"
                                value={bankInfoForm.bank_name}
                                onChange={(e) => setBankInfoForm({...bankInfoForm, bank_name: e.target.value})}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Enter bank name"
                            />
                                </div>
								<div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Account Owner Name</label>
                            <input
                                type="text"
                                value={bankInfoForm.bank_account_owner}
                                onChange={(e) => setBankInfoForm({...bankInfoForm, bank_account_owner: e.target.value})}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Enter account owner name"
                            />
                            </div>
						</div>
                                        <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Bank Account Number</label>
                        <input
                            type="text"
                            value={bankInfoForm.bank_account_number}
                            onChange={(e) => setBankInfoForm({...bankInfoForm, bank_account_number: e.target.value})}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Enter bank account number"
                        />
					</div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input
                            type="password"
                            value={bankInfoForm.current_password}
                            onChange={(e) => setBankInfoForm({...bankInfoForm, current_password: e.target.value})}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Enter current password to confirm"
                        />
                        </div>
                    </div>
                    <div className="flex justify-end gap-3 pt-4">
                                    <button 
                            onClick={() => setShowBankInfoModal(false)}
                            className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                                    >
                            Cancel
                                    </button>
                                <button
                            onClick={handleBankInfoUpdate}
                            className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                >
                            Update Bank Info
                                </button>
                            </div>
                            </div>
            </Modal>
                            
            {/* Update Profile Modal */}
            <Modal open={showUpdateProfileModal} onClose={() => setShowUpdateProfileModal(false)} title="Update Profile" maxWidth="max-w-2xl">
                <div className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                        <input
                                            type="text"
                                            value={profileForm.name}
                                            onChange={(e) => setProfileForm({...profileForm, name: e.target.value})}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Enter your name"
                                        />
                                    </div>
                                    <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input
                                            type="email"
                                            value={profileForm.email}
                                            onChange={(e) => setProfileForm({...profileForm, email: e.target.value})}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Enter your email"
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                                        <input
                                            type="text"
                                            value={profileForm.streetAddress}
                                            onChange={(e) => setProfileForm({...profileForm, streetAddress: e.target.value})}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Enter your street address"
                                        />
                                    </div>
								<div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                                        <input
                                            type="text"
                                            value={profileForm.postalCode}
                                            onChange={(e) => setProfileForm({...profileForm, postalCode: e.target.value})}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Enter postal code"
                                        />
								</div>
								<div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">State</label>
                                        <select
                                            value={profileForm.state}
                                            onChange={(e) => setProfileForm({...profileForm, state: e.target.value})}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                        >
                                <option value="">Select State</option>
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
                            <label className="block text-sm font-medium text-gray-700 mb-1">City</label>
                                        <input
                                            type="text"
                                            value={profileForm.city}
                                            onChange={(e) => setProfileForm({...profileForm, city: e.target.value})}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Enter your city"
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 mb-1">Account Password</label>
                                        <input
                                            type="password"
                                            value={profileForm.accountPassword}
                                            onChange={(e) => setProfileForm({...profileForm, accountPassword: e.target.value})}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Enter your account password"
                                        />
                                        <p className="text-sm text-gray-500 mt-1">Please enter your account password to update profile</p>
                                    </div>
                                </div>
                    <div className="flex justify-end gap-3 pt-4">
                                    <button
                                        onClick={() => setShowUpdateProfileModal(false)}
                            className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={handleSubmitUpdateProfile}
                                        className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                    >
                            Update Profile
                                    </button>
                                </div>
                            </div>
            </Modal>

            {/* Change Phone Modal */}
            <Modal open={showChangePhoneModal} onClose={() => {
                                        setShowChangePhoneModal(false);
                                        resetPhoneChange();
            }} title="Change Email" maxWidth="max-w-2xl">
                <div className="space-y-6">
                                {phoneChangeStep === 'initial' && (
                        <div className="text-center space-y-4">
                            <div className="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto">
                                <Mail className="w-8 h-8 text-emerald-600" />
                                                </div>
                            <div>
                                            <h3 className="text-lg font-semibold text-gray-800">Change Email</h3>
                                <p className="text-gray-600 mt-2">
                                                To change your email, first verify your current email. Click Continue to have the verification code sent to your registered email.
                                            </p>
                            </div>
                                            <button
                                                onClick={handlePhoneContinue}
                                                className="w-full px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium"
                                            >
                                                Continue
                                            </button>
                                    </div>
                                )}

                                {phoneChangeStep === 'tac-verification' && (
                        <div className="space-y-4">
                            <div className="text-center">
                                <div className="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto">
                                    <Shield className="w-8 h-8 text-emerald-600" />
                                                </div>
                                <h3 className="text-lg font-semibold text-gray-800 mt-4">Email Verification</h3>
                                <p className="text-gray-600 mt-2">Enter the verification code sent to your current email.</p>
                                            </div>
                                            <input
                                                type="text"
                                                value={tacCode}
                                                onChange={(e) => setTacCode(e.target.value)}
                                placeholder="Enter verification code"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <div className="flex gap-3">
                                <button
                                    onClick={handlePhoneBack}
                                    className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Back
                                </button>
                                            <button
                                                onClick={handlePhoneContinue}
                                    className="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                            >
                                                Continue
                                            </button>
                            </div>
                                            <button className="w-full text-emerald-600 hover:text-emerald-700 text-sm">
                                                Resend TAC
                                            </button>
                                    </div>
                                )}

                                {phoneChangeStep === 'new-phone' && (
                        <div className="space-y-4">
                            <div className="text-center">
                                <div className="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto"><Mail className="w-8 h-8 text-emerald-600" /></div>
                                <h3 className="text-lg font-semibold text-gray-800 mt-4">Enter New Email</h3>
                                <p className="text-gray-600 mt-2">Enter your new email. We will send a code to verify it.</p>
                                                </div>
                                            <input
                                                type="email"
                                                value={newPhoneNumber}
                                                onChange={(e) => setNewPhoneNumber(e.target.value)}
                                                placeholder="your@email.com"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <div className="flex gap-3">
                                <button
                                    onClick={handlePhoneBack}
                                    className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Back
                                </button>
                                            <button
                                                onClick={handlePhoneContinue}
                                    className="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                            >
                                                Continue
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {phoneChangeStep === 'new-tac-verification' && (
                        <div className="space-y-4">
                            <div className="text-center">
                                <div className="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto">
                                    <Shield className="w-8 h-8 text-emerald-600" />
                                                </div>
                                <h3 className="text-lg font-semibold text-gray-800 mt-4">Verify New Email</h3>
                                <p className="text-gray-600 mt-2">Enter the code sent to your new email to finish.</p>
                                            </div>
                                            <input
                                                type="text"
                                                value={newTacCode}
                                                onChange={(e) => setNewTacCode(e.target.value)}
                                placeholder="Enter verification code"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <div className="flex gap-3">
                                <button
                                    onClick={handlePhoneBack}
                                    className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Back
                                </button>
                                            <button
                                                onClick={handlePhoneContinue}
                                    className="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                            >
                                                Continue
                                            </button>
                            </div>
                                            <button className="w-full text-emerald-600 hover:text-emerald-700 text-sm">
                                                Resend TAC
                                            </button>
                                    </div>
                                )}

                                {phoneChangeStep === 'success' && (
                        <div className="text-center space-y-4">
                            <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                                <CheckCircle className="w-8 h-8 text-green-600" />
                                            </div>
                            <div>
                                <h3 className="text-lg font-semibold text-gray-800">Email Updated</h3>
                                <p className="text-gray-600 mt-2">Your email has been updated and verified successfully.</p>
                                        </div>
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
                                )}

                    {/* Progress indicators */}
                    <div className="flex justify-center">
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
            </Modal>

            {/* Change Password Modal */}
            <Modal open={showChangePasswordModal} onClose={() => setShowChangePasswordModal(false)} title="Change Password" maxWidth="max-w-md">
                <div className="space-y-4">
								<div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Old Password</label>
                                    <input
                                        type="password"
                                        value={oldPassword}
                                        onChange={(e) => setOldPassword(e.target.value)}
                            placeholder="Enter your old password"
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
								</div>
								<div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <input
                                        type="password"
                                        value={newPassword}
                                        onChange={(e) => setNewPassword(e.target.value)}
                            placeholder="Enter your new password"
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
								</div>
								<div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                    <input
                                        type="password"
                                        value={confirmPassword}
                                        onChange={(e) => setConfirmPassword(e.target.value)}
                            placeholder="Confirm your new password"
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                                {passwordError && (
                                    <div className="bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p className="text-red-800 text-sm">{passwordError}</p>
                                    </div>
                                )}
                    <div className="flex justify-end gap-3 pt-4">
                                    <button
                                        onClick={() => setShowChangePasswordModal(false)}
                            className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={handlePasswordUpdate}
                                        className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                    >
                            Change Password
                                    </button>
                                </div>
                            </div>
            </Modal>

            {/* View Policy Modal */}
            <Modal open={showPolicyModal} onClose={() => setShowPolicyModal(false)} title="Policy Details" maxWidth="max-w-4xl">
                {selectedPolicy && (
                    <div className="space-y-6">
                        <div className={`bg-gradient-to-r ${selectedPolicy.gradient || 'from-emerald-500 to-emerald-600'} rounded-lg p-6 text-white`}>
                            <h3 className="text-2xl font-bold mb-2">{selectedPolicy.planName}</h3>
                            <p className="text-emerald-100">{selectedPolicy.description}</p>
                            </div>
                            
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-4">
                                <h4 className="text-lg font-semibold text-gray-800">Plan Information</h4>
                                <div className="space-y-3">
                                                <div className="flex justify-between">
                                        <span className="text-gray-600">Plan Type:</span>
                                        <span className="font-semibold">{selectedPolicy.type}</span>
                                                </div>
                                                <div className="flex justify-between">
                                        <span className="text-gray-600">Premium:</span>
                                        <span className="font-semibold">{selectedPolicy.premium}</span>
                                                </div>
                                                <div className="flex justify-between">
                                        <span className="text-gray-600">Coverage Period:</span>
                                        <span className="font-semibold">{selectedPolicy.coveragePeriod}</span>
                                                </div>
                                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Status:</span>
                                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                            selectedPolicy.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        }`}>
                                            {selectedPolicy.status}
                                        </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                <h4 className="text-lg font-semibold text-gray-800">Benefits Summary</h4>
                                        <div className="bg-gray-50 rounded-lg p-4">
                                    <div className="space-y-2 text-sm">
                                        {selectedPolicy.benefits.map((benefit: any, index: number) => (
                                            <div key={index} className="flex justify-between">
                                                <span className="text-gray-600">{benefit.name}:</span>
                                                <span className="font-semibold">{benefit.amount}</span>
                                                </div>
                                        ))}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        <div className="space-y-4">
                            <h4 className="text-lg font-semibold text-gray-800">Detailed Benefits</h4>
                            <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table className="w-full">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Benefit</th>
                                            <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Amount (RM)</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {selectedPolicy.detailedBenefits.map((benefit: any, index: number) => (
                                            <tr key={index}>
                                                <td className="px-4 py-3 text-sm text-gray-800">{benefit.name}</td>
                                                <td className="px-4 py-3 text-sm font-semibold text-gray-800">{benefit.amount}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                                        </div>
                                    </div>

                        <div className="space-y-4">
                            <h4 className="text-lg font-semibold text-gray-800">Terms & Conditions</h4>
                            <div className="bg-gray-50 rounded-lg p-4">
                                <ul className="space-y-2 text-sm text-gray-700">
                                    {selectedPolicy.terms.map((term: string, index: number) => (
                                        <li key={index} className="flex items-start">
                                            <span className="text-emerald-600 mr-2">•</span>
                                            <span>{term}</span>
                                        </li>
                                    ))}
                                </ul>
                                </div>
                            </div>

                        <div className="flex justify-end gap-3 pt-4">
                            <a href="https://kh-berhad.com/medical-card/" target="_blank" rel="noopener noreferrer" className="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">Open Policy Docs</a>
                            <button 
                                onClick={() => handlePrintPolicy(selectedPolicy)}
                                className={`px-4 py-2 text-white rounded-lg transition-colors flex items-center gap-2 ${selectedPolicy.gradient ? 'bg-gradient-to-r ' + selectedPolicy.gradient : 'bg-emerald-600 hover:bg-emerald-700'}`}
                            >
                                <Printer className="w-4 h-4" />
                                Print Policy
                            </button>
                            <button
                                onClick={() => setShowPolicyModal(false)}
                                className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                )}
            </Modal>

            {/* Receipt Modal (Payments) */}
            <Modal open={showReceiptModal && !!selectedPayment} onClose={() => setShowReceiptModal(false)} title="Official Receipt" maxWidth="max-w-lg">
                {selectedPayment && (
                    <div className="space-y-4">
                                <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 bg-blue-600 rounded-full grid place-content-center text-white font-bold">W</div>
                                        <div>
                                    <div className="font-semibold text-gray-800">WE KONGSI</div>
                                    <div className="text-xs text-gray-600">Kita Kongsi Sdn Bhd (1492373-D)</div>
                                        </div>
                                    </div>
                            <div className="text-sm text-gray-500">{selectedPayment.date}</div>
                                </div>
                        <div className="divide-y divide-gray-200 text-sm">
                            <div className="flex justify-between py-2"><span className="text-gray-600">Description</span><span className="font-medium">{selectedPayment.description}</span></div>
                            <div className="flex justify-between py-2"><span className="text-gray-600">Amount</span><span className="font-semibold">RM {selectedPayment.amount}</span></div>
                            <div className="flex justify-between py-2"><span className="text-gray-600">Method</span><span className="font-medium">{selectedPayment.method}</span></div>
                            <div className="flex justify-between py-2"><span className="text-gray-600">Status</span><span className="font-medium">{selectedPayment.status}</span></div>
                                </div>
                        <div className="flex gap-3 justify-end pt-2">
                            <button onClick={() => window.print()} className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">Download PDF</button>
                            </div>
                                        </div>
                )}
            </Modal>

            {/* Client Details Modal (Medical Insurance) */}
            <Modal open={showClientModal} onClose={() => setShowClientModal(false)} title="Client Details" maxWidth="max-w-3xl">
                {selectedClient && (
                    <div className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div className="text-sm text-gray-600">Full Name</div>
                                <div className="font-semibold text-gray-800">{selectedClient.full_name}</div>
                                        </div>
                            <div>
                                <div className="text-sm text-gray-600">NRIC</div>
                                <div className="font-semibold text-gray-800">{selectedClient.nric || '-'}</div>
                                        </div>
                            <div>
                                <div className="text-sm text-gray-600">Plan</div>
                                <div className="font-semibold text-gray-800">{selectedClient.plan_name || selectedClient.plan || '-'}</div>
                                        </div>
                            <div>
                                <div className="text-sm text-gray-600">Status</div>
                                <div className="font-semibold text-gray-800">{selectedClient.status || '-'}</div>
                                        </div>
                                        </div>
                        <div className="flex justify-end gap-3">
                            <button onClick={() => handleDownloadClientCard(selectedClient)} className="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition-colors">Download Card</button>
                                        </div>
                                    </div>
                )}
            </Modal>

            {/* View User Modal */}
            <Modal open={showViewUserModal} onClose={() => setShowViewUserModal(false)} title="User Details" maxWidth="max-w-5xl">
                {selectedUser && (
                    <div className="space-y-6">
                        <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white flex items-center justify-between">
                            <div>
                                <h3 className="text-2xl font-bold mb-1">{selectedUser.name}</h3>
                                <div className="text-blue-100 text-sm">Agent Code: {selectedUser.agent_code || 'N/A'}</div>
                            </div>
                            <div className="flex gap-3">
                                {[1,2,3,4,5].map((lvl) => (
                                    <button
                                        key={lvl}
                                        onClick={() => handleChangeSelectedUserLevel(lvl)}
                                        className={`px-3 py-1 rounded-full text-sm ${selectedUserLevelFilter === lvl ? 'bg-white text-blue-700' : 'bg-blue-600 text-blue-100 hover:bg-blue-500'}`}
                                    >
                                        L{lvl}
                                    </button>
                                ))}
                                <button
                                    onClick={() => handleChangeSelectedUserLevel(undefined)}
                                    className={`px-3 py-1 rounded-full text-sm ${selectedUserLevelFilter === undefined ? 'bg-white text-blue-700' : 'bg-blue-600 text-blue-100 hover:bg-blue-500'}`}
                                >
                                    All
                                </button>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div className="lg:col-span-2 space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="bg-white border border-gray-200 rounded-lg p-5">
                                        <h4 className="text-base font-semibold text-gray-800 mb-3">Personal Information</h4>
                                        <div className="space-y-2 text-sm">
                                            <div className="flex justify-between"><span className="text-gray-600">Email</span><span className="font-medium">{selectedUser.email || '-'}</span></div>
                                            <div className="flex justify-between"><span className="text-gray-600">Phone</span><span className="font-medium">{selectedUser.phone_number || '-'}</span></div>
                                            <div className="flex justify-between"><span className="text-gray-600">NRIC</span><span className="font-medium">{selectedUser.nric || '-'}</span></div>
                                        </div>
                                    </div>
                                    <div className="bg-white border border-gray-200 rounded-lg p-5">
                                        <h4 className="text-base font-semibold text-gray-800 mb-3">Account</h4>
                                        <div className="space-y-2 text-sm">
                                            <div className="flex justify-between"><span className="text-gray-600">Status</span><span className={`px-2 py-0.5 rounded-full text-xs font-medium ${selectedUser.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>{selectedUser.status || 'Active'}</span></div>
                                            <div className="flex justify-between"><span className="text-gray-600">Level</span><span className="font-medium">{selectedUser.mlm_level || 0}</span></div>
                                            <div className="flex justify-between"><span className="text-gray-600">Join Date</span><span className="font-medium">{selectedUser.created_at ? new Date(selectedUser.created_at).toLocaleDateString() : 'N/A'}</span></div>
                                        </div>
                                    </div>
                                    <div className="md:col-span-2 bg-white border border-gray-200 rounded-lg p-5">
                                        <h4 className="text-base font-semibold text-gray-800 mb-3">Address</h4>
                                        <div className="text-sm text-gray-700">{selectedUser.address || 'No address provided'}</div>
                                        {(selectedUser.city || selectedUser.state || selectedUser.postal_code) && (
                                            <div className="mt-1 text-sm text-gray-600">{[selectedUser.city, selectedUser.state].filter(Boolean).join(', ')} {selectedUser.postal_code || ''}</div>
                                        )}
                                    </div>
                                </div>

                                <div className="bg-white border border-gray-200 rounded-lg p-5">
                                    <div className="flex items-center justify-between mb-3">
                                        <h4 className="text-base font-semibold text-gray-800">Downlines {selectedUserLevelFilter ? `(Level ${selectedUserLevelFilter})` : ''}</h4>
                                        {selectedUserByLevelCounts && (
                                            <div className="flex gap-2 text-xs text-gray-600">
                                                {Object.entries(selectedUserByLevelCounts).map(([lvl, count]) => (
                                                    <div key={lvl} className="px-2 py-0.5 bg-gray-100 rounded-full">L{lvl}: {count}</div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                    {isSelectedUserLoading ? (
                                        <div className="flex items-center justify-center py-10 text-gray-500 text-sm">Loading downlines...</div>
                                    ) : selectedUserDownlines.length === 0 ? (
                                        <div className="text-center py-10 text-gray-500 text-sm">No downlines found</div>
                                    ) : (
                                        <div className="space-y-2 max-h-72 overflow-y-auto">
                                            {selectedUserDownlines.map((d: any, idx: number) => (
                                                <div key={idx} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                    <div>
                                                        <div className="font-medium text-gray-800">{d.agent?.name || '-'}</div>
                                                        <div className="text-xs text-gray-500">{d.agent?.agent_code || d.agent_code || '-'}</div>
                                                    </div>
                                                    <div className="text-xs px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 capitalize">{d.status || '-'}</div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </div>
                            <div className="space-y-6">
                                <div className="bg-white border border-gray-200 rounded-lg p-5">
                                    <h4 className="text-base font-semibold text-gray-800 mb-2">Commission Summary</h4>
                                    {selectedUserCommissionSummary ? (
                                        <div className="text-sm space-y-2">
                                            <div className="flex justify-between"><span className="text-gray-600">Total</span><span className="font-semibold">RM {selectedUserCommissionSummary.total}</span></div>
                                            <div className="flex justify-between"><span className="text-gray-600">Current Month</span><span className="font-semibold">RM {selectedUserCommissionSummary.current_month}</span></div>
                                            <div className="flex justify-between"><span className="text-gray-600">Pending</span><span className="font-semibold">RM {selectedUserCommissionSummary.pending}</span></div>
                                        </div>
                                    ) : (
                                        <div className="text-sm text-gray-500">No commission data</div>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-end">
                            <button 
                                onClick={() => setShowViewUserModal(false)}
                                className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                )}
            </Modal>
        </PageTransition>
	);
}

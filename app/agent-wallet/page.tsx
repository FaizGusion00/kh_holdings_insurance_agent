"use client";

import { useState, useEffect } from "react";
import { useAuth } from "../contexts/AuthContext";
import { apiService } from "@/app/services/api";
import {
  Wallet,
  DollarSign,
  Clock,
  CheckCircle,
  XCircle,
  Plus,
  ArrowUpRight,
  ArrowDownLeft,
  Eye,
  RefreshCw,
  AlertCircle,
} from "lucide-react";
import {
  PageTransition,
  StaggeredContainer,
  StaggeredItem,
} from "../(ui)/components/PageTransition";
import { motion, AnimatePresence } from "framer-motion";

interface WalletData {
  balance: number;
  pending_commissions: number;
  total_earned: number;
  recent_transactions: Transaction[];
  withdrawal_requests: WithdrawalRequest[];
}

interface Transaction {
  id: number;
  type: "credit" | "debit" | "adjustment";
  amount: number;
  description: string;
  status: "completed" | "pending" | "failed";
  created_at: string;
  commission_id?: number;
}

interface WithdrawalRequest {
  id: number;
  amount: number;
  status: "pending" | "approved" | "rejected" | "completed";
  created_at: string;
  processed_at?: string;
  admin_notes?: string;
  proof_url?: string;
}

export default function AgentWalletPage() {
  const { user, isAuthenticated, isLoading } = useAuth();
  const [walletData, setWalletData] = useState<WalletData | null>(null);
  const [isWalletLoading, setIsWalletLoading] = useState(true);
  const [error, setError] = useState("");
  const [showWithdrawalModal, setShowWithdrawalModal] = useState(false);
  const [withdrawalAmount, setWithdrawalAmount] = useState("");
  const [withdrawalNotes, setWithdrawalNotes] = useState("");

  useEffect(() => {
    if (isAuthenticated) {
      loadWalletData();
    }
  }, [isAuthenticated]);

  const loadWalletData = async () => {
    try {
      setIsWalletLoading(true);
      setError("");

      const response = await apiService.getAgentWallet();

      if (response.success && response.data) {
        setWalletData(response.data);
      } else {
        setError(response.message || "Failed to load wallet data");
      }
    } catch (err) {
      setError(
        err instanceof Error ? err.message : "Failed to load wallet data"
      );
    } finally {
      setIsWalletLoading(false);
    }
  };

  const handleWithdrawalRequest = async () => {
    if (!withdrawalAmount || parseFloat(withdrawalAmount) <= 0) {
      setError("Please enter a valid withdrawal amount");
      return;
    }

    if (walletData && parseFloat(withdrawalAmount) > walletData.balance) {
      setError("Insufficient balance for withdrawal");
      return;
    }

    try {
      const response = await apiService.requestWithdrawal({
        amount: parseFloat(withdrawalAmount),
        notes: withdrawalNotes,
      });

      if (response.success) {
        setShowWithdrawalModal(false);
        setWithdrawalAmount("");
        setWithdrawalNotes("");
        setError("");
        loadWalletData(); // Refresh
      } else {
        setError(response.message || "Failed to submit withdrawal request");
      }
    } catch (err) {
      setError(
        err instanceof Error
          ? err.message
          : "Failed to submit withdrawal request"
      );
    }
  };

  const formatCurrency = (amount: number) => {
    return `RM ${amount.toLocaleString("en-MY", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })}`;
  };

  const formatDate = (dateString: string) => {
    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) {
        return "Invalid Date";
      }
      return date.toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
    } catch {
      return "Invalid Date";
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case "completed":
      case "approved":
        return "text-green-600 bg-green-100";
      case "pending":
        return "text-yellow-600 bg-yellow-100";
      case "rejected":
      case "failed":
        return "text-red-600 bg-red-100";
      default:
        return "text-gray-600 bg-gray-100";
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case "completed":
      case "approved":
        return <CheckCircle className="w-4 h-4" />;
      case "pending":
        return <Clock className="w-4 h-4" />;
      case "rejected":
      case "failed":
        return <XCircle className="w-4 h-4" />;
      default:
        return <AlertCircle className="w-4 h-4" />;
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4" />
          <p className="text-gray-600">Loading...</p>
        </div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-gray-900 mb-4">
            Please Login
          </h2>
          <p className="text-gray-600">
            You need to be logged in to view your wallet.
          </p>
        </div>
      </div>
    );
  }

  return (
    <PageTransition>
      <div className="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div className="max-w-5xl mx-auto">
          <div className="border-2 border-green-200 rounded-xl p-4 sm:p-6 bg-white shadow-sm">
            {/* Header */}
            <div className="mb-6">
              <h1 className="text-2xl font-bold text-gray-900 mb-1">
                Agent Wallet
              </h1>
              <p className="text-sm text-gray-600">
                Track your commissions and manage withdrawals
              </p>
            </div>

            {isWalletLoading ? (
              <div className="flex items-center justify-center py-12">
                <RefreshCw className="w-6 h-6 animate-spin text-blue-600" />
                <span className="ml-2 text-gray-600">Loading wallet data...</span>
              </div>
            ) : error ? (
              <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div className="flex items-center">
                  <AlertCircle className="w-5 h-5 text-red-600 mr-2" />
                  <span className="text-red-800">{error}</span>
                </div>
              </div>
            ) : walletData ? (
              <StaggeredContainer>
                <StaggeredItem>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    {/* Cards */}
                    <div className="bg-white border rounded-lg p-4 flex justify-between items-center">
                      <div>
                        <p className="text-sm text-gray-600">Current Balance</p>
                        <p className="text-xl font-bold text-gray-900">
                          {formatCurrency(walletData.balance)}
                        </p>
                      </div>
                      <div className="bg-green-100 p-2 rounded-lg">
                        <Wallet className="text-green-600 w-5 h-5" />
                      </div>
                    </div>
                    <div className="bg-white border rounded-lg p-4 flex justify-between items-center">
                      <div>
                        <p className="text-sm text-gray-600">
                          Pending Commissions
                        </p>
                        <p className="text-xl font-bold text-yellow-600">
                          {formatCurrency(walletData.pending_commissions)}
                        </p>
                      </div>
                      <div className="bg-yellow-100 p-2 rounded-lg">
                        <Clock className="text-yellow-600 w-5 h-5" />
                      </div>
                    </div>
                    <div className="bg-white border rounded-lg p-4 flex justify-between items-center">
                      <div>
                        <p className="text-sm text-gray-600">Total Earned</p>
                        <p className="text-xl font-bold text-blue-600">
                          {formatCurrency(walletData.total_earned)}
                        </p>
                      </div>
                      <div className="bg-blue-100 p-2 rounded-lg">
                        <DollarSign className="text-blue-600 w-5 h-5" />
                      </div>
                    </div>
                  </div>
                </StaggeredItem>

                {/* Action buttons */}
                <StaggeredItem>
                  <div className="flex flex-col sm:flex-row gap-3 mb-6">
                    <button
                      onClick={() => setShowWithdrawalModal(true)}
                      disabled={walletData.balance <= 0}
                      className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-300"
                    >
                      <ArrowUpRight className="w-4 h-4 inline mr-2" />
                      Request Withdrawal
                    </button>
                    <button
                      onClick={loadWalletData}
                      className="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                    >
                      <RefreshCw className="w-4 h-4 inline mr-2" />
                      Refresh
                    </button>
                  </div>
                </StaggeredItem>

                {/* Transactions and Withdrawals */}
                <StaggeredItem>
                  <div className="grid md:grid-cols-2 gap-4">
                    {/* Transactions */}
                    <div className="bg-white border rounded-lg">
                      <div className="p-4 border-b">
                        <h3 className="font-semibold text-gray-800">
                          Recent Transactions
                        </h3>
                      </div>
                      <div className="p-4 space-y-4">
                        {walletData.recent_transactions.length > 0 ? (
                          walletData.recent_transactions.map((t) => (
                            <div
                              key={t.id}
                              className="flex justify-between items-start bg-gray-50 p-3 rounded-md"
                            >
                              <div className="flex items-center">
                                <div
                                  className={`w-8 h-8 rounded-md flex items-center justify-center mr-3 ${
                                    t.type === "credit"
                                      ? "bg-green-100"
                                      : "bg-red-100"
                                  }`}
                                >
                                  {t.type === "credit" ? (
                                    <ArrowDownLeft className="text-green-600 w-4 h-4" />
                                  ) : (
                                    <ArrowUpRight className="text-red-600 w-4 h-4" />
                                  )}
                                </div>
                                <div>
                                  <p className="text-sm font-medium text-gray-800">
                                    {t.description}
                                  </p>
                                  <p className="text-xs text-gray-500">
                                    {formatDate(t.created_at)}
                                  </p>
                                </div>
                              </div>
                              <div className="text-right">
                                <p
                                  className={`text-sm font-semibold ${
                                    t.type === "credit"
                                      ? "text-green-600"
                                      : "text-red-600"
                                  }`}
                                >
                                  {t.type === "credit" ? "+" : "-"}
                                  {formatCurrency(t.amount)}
                                </p>
                                <span
                                  className={`inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs ${getStatusColor(
                                    t.status
                                  )}`}
                                >
                                  {getStatusIcon(t.status)}
                                  <span className="ml-1 capitalize">
                                    {t.status}
                                  </span>
                                </span>
                              </div>
                            </div>
                          ))
                        ) : (
                          <p className="text-sm text-gray-500 text-center">
                            No transactions yet.
                          </p>
                        )}
                      </div>
                    </div>

                    {/* Withdrawal Requests */}
                    <div className="bg-white border rounded-lg">
                      <div className="p-4 border-b">
                        <h3 className="font-semibold text-gray-800">
                          Withdrawal Requests
                        </h3>
                      </div>
                      <div className="p-4 space-y-4">
                        {walletData.withdrawal_requests.length > 0 ? (
                          walletData.withdrawal_requests.map((r) => (
                            <div
                              key={r.id}
                              className="p-3 bg-gray-50 rounded-md flex justify-between"
                            >
                              <div>
                                <p className="text-sm font-medium text-gray-800">
                                  {formatCurrency(r.amount)}
                                </p>
                                <p className="text-xs text-gray-500">
                                  Requested: {formatDate(r.created_at)}
                                </p>
                                {r.processed_at && (
                                  <p className="text-xs text-gray-500">
                                    Processed: {formatDate(r.processed_at)}
                                  </p>
                                )}
                                {r.admin_notes && (
                                  <p className="text-xs text-gray-600 mt-1">
                                    Note: {r.admin_notes}
                                  </p>
                                )}
                              </div>
                              <div className="text-right">
                                <span
                                  className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs ${getStatusColor(
                                    r.status
                                  )}`}
                                >
                                  {getStatusIcon(r.status)}
                                  <span className="ml-1 capitalize">
                                    {r.status}
                                  </span>
                                </span>
                                {r.proof_url && (
                                  <a
                                    href={r.proof_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="block text-blue-600 hover:underline mt-1 text-xs"
                                  >
                                    <Eye className="w-3 h-3 inline mr-1" />
                                    View Proof
                                  </a>
                                )}
                              </div>
                            </div>
                          ))
                        ) : (
                          <p className="text-sm text-gray-500 text-center">
                            No withdrawal requests yet.
                          </p>
                        )}
                      </div>
                    </div>
                  </div>
                </StaggeredItem>
              </StaggeredContainer>
            ) : null}
          </div>
        </div>
      </div>

      {/* Modal */}
      <AnimatePresence>
        {showWithdrawalModal && (
          <motion.div
            className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
          >
            <motion.div
              className="bg-white rounded-lg p-6 w-full max-w-md"
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.9, opacity: 0 }}
            >
              <h3 className="text-lg font-semibold text-gray-800 mb-4">
                Request Withdrawal
              </h3>
              <div className="space-y-4">
                <div>
                  <label className="text-sm font-medium text-gray-700 block mb-1">
                    Amount (RM)
                  </label>
                  <input
                    type="number"
                    value={withdrawalAmount}
                    onChange={(e) => setWithdrawalAmount(e.target.value)}
                    placeholder="0.00"
                    className="w-full border px-3 py-2 rounded-md"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium text-gray-700 block mb-1">
                    Notes (optional)
                  </label>
                  <textarea
                    value={withdrawalNotes}
                    onChange={(e) => setWithdrawalNotes(e.target.value)}
                    className="w-full border px-3 py-2 rounded-md"
                  />
                </div>
              </div>
              <div className="flex gap-3 mt-6">
                <button
                  onClick={() => setShowWithdrawalModal(false)}
                  className="flex-1 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                >
                  Cancel
                </button>
                <button
                  onClick={handleWithdrawalRequest}
                  className="flex-1 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                  Submit
                </button>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </PageTransition>
  );
}

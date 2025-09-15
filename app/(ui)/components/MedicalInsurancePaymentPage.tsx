"use client";

import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { CheckCircle, CreditCard, Shield, Clock, X } from "lucide-react";
import { apiService } from "../../services/api";

interface MedicalInsurancePlan {
  id: number;
  name: string;
  description: string;
  monthly_price: number;
  quarterly_price: number | null;
  half_yearly_price: number | null;
  yearly_price: number;
  commitment_fee: number;
  coverage_details: any;
  max_age: number | null;
  min_age: number;
}

interface PaymentPageProps {
  registrationId: number;
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (payment: any) => void;
}

export default function MedicalInsurancePaymentPage({ 
  registrationId, 
  isOpen, 
  onClose, 
  onSuccess 
}: PaymentPageProps) {
  const [plans, setPlans] = useState<MedicalInsurancePlan[]>([]);
  const [selectedPlan, setSelectedPlan] = useState<MedicalInsurancePlan | null>(null);
  const [paymentFrequency, setPaymentFrequency] = useState<'monthly' | 'quarterly' | 'half_yearly' | 'yearly'>('monthly');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [paymentConfig, setPaymentConfig] = useState<any>(null);
  const [showPaymentForm, setShowPaymentForm] = useState(false);
  const [registrationDetails, setRegistrationDetails] = useState<any>(null);
  const [totalAmount, setTotalAmount] = useState<number | null>(null);
  const [amountBreakdown, setAmountBreakdown] = useState<any[]>([]);

  useEffect(() => {
    if (isOpen) {
      loadPlans();
      loadPaymentConfig();
      loadRegistrationDetails();
    }
  }, [isOpen]);

  const loadPlans = async () => {
    try {
      const response = await apiService.getMedicalInsurancePlans();
      if (response.success && response.data) {
        setPlans(response.data);
        if (response.data.length > 0) {
          setSelectedPlan(response.data[0]);
        }
      }
    } catch (err) {
      setError("Failed to load medical insurance plans");
    }
  };

  const loadPaymentConfig = async () => {
    try {
      const response = await apiService.getMedicalInsurancePaymentConfig();
      if (response.success && response.data) {
        setPaymentConfig(response.data);
      }
    } catch (err) {
      setError("Failed to load payment configuration");
    }
  };

  const handlePlanSelect = (plan: MedicalInsurancePlan) => {
    setSelectedPlan(plan);
  };

  const handlePaymentFrequencyChange = (frequency: 'monthly' | 'quarterly' | 'half_yearly' | 'yearly') => {
    setPaymentFrequency(frequency);
  };

  const getPlanPrice = (plan: MedicalInsurancePlan, frequency: string) => {
    switch (frequency) {
      case 'monthly':
        return plan.monthly_price;
      case 'quarterly':
        return plan.quarterly_price || 0;
      case 'half_yearly':
        return plan.half_yearly_price || 0;
      case 'yearly':
        return plan.yearly_price;
      default:
        return plan.monthly_price;
    }
  };

  const getTotalPrice = (plan: MedicalInsurancePlan, frequency: string) => {
    const basePrice = getPlanPrice(plan, frequency);
    return basePrice + plan.commitment_fee;
  };

  const loadRegistrationDetails = async () => {
    if (!registrationId) return;
    
    try {
      // Get registration details
      const response = await apiService.getMedicalInsuranceRegistrationStatus(registrationId);
      if (response.success && response.data) {
        setRegistrationDetails(response.data);
        
        // Pre-calculate total amount
        const preCalcResponse = await apiService.createMedicalInsurancePaymentOrderForAllCustomers({
          registration_id: registrationId,
          calculate_only: true
        });
        
        if (preCalcResponse.success && preCalcResponse.data?.total_amount !== undefined) {
          setTotalAmount(preCalcResponse.data.total_amount);
          setAmountBreakdown(preCalcResponse.data.breakdown || []);
        }
      }
    } catch (err) {
      console.error("Failed to load registration details", err);
    }
  };

  const handleProceedToPayment = async () => {
    setLoading(true);
    setError("");

    try {
      const response = await apiService.createMedicalInsurancePaymentOrderForAllCustomers({
        registration_id: registrationId
      });

      if (response.success && response.data) {
        // Initialize Razorpay checkout
        const options = {
          key: paymentConfig?.key_id,
          amount: response.data.checkout_config.amount,
          currency: response.data.checkout_config.currency,
          name: response.data.checkout_config.name,
          description: response.data.checkout_config.description,
          order_id: response.data.checkout_config.order_id,
          prefill: response.data.checkout_config.prefill,
          theme: response.data.checkout_config.theme,
          handler: async function (response: any) {
            // Verify payment
            try {
              const verifyResponse = await apiService.verifyMedicalInsurancePayment({
                razorpay_order_id: response.razorpay_order_id,
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_signature: response.razorpay_signature,
                registration_id: registrationId
              });

              if (verifyResponse.success) {
                onSuccess(verifyResponse.data);
                // Show a lightweight confirmation before closing
                alert('Payment successful and verified. Thank you!');
                onClose();
              } else {
                setError(verifyResponse.message || "Payment verification failed");
              }
            } catch (err) {
              setError(err instanceof Error ? err.message : "Payment verification failed");
            }
          },
          modal: {
            ondismiss: function() {
              setLoading(false);
            }
          }
        };

        const razorpay = new (window as any).Razorpay(options);
        razorpay.open();
        // Sync UI with authoritative backend total and breakdown
        setTotalAmount(response.data.amount);
        setAmountBreakdown(response.data.breakdown || []);
      } else {
        setError(response.message || "Failed to create payment order");
      }
    } catch (err) {
      setError("Payment initialization failed");
    } finally {
      setLoading(false);
    }
  };

  const renderPlanCard = (plan: MedicalInsurancePlan) => (
    <motion.div
      key={plan.id}
      whileHover={{ scale: 1.02 }}
      whileTap={{ scale: 0.98 }}
      className={`cursor-pointer rounded-xl border-2 p-6 transition-all duration-200 ${
        selectedPlan?.id === plan.id
          ? 'border-emerald-500 bg-emerald-50'
          : 'border-gray-200 bg-white hover:border-gray-300'
      }`}
      onClick={() => handlePlanSelect(plan)}
    >
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-semibold text-gray-800">{plan.name}</h3>
        {selectedPlan?.id === plan.id && (
          <CheckCircle className="w-6 h-6 text-emerald-600" />
        )}
      </div>
      
      <p className="text-sm text-gray-600 mb-4">{plan.description}</p>
      
      <div className="space-y-2">
        <div className="flex justify-between">
          <span className="text-sm text-gray-600">Monthly:</span>
          <span className="font-semibold">RM {plan.monthly_price}</span>
        </div>
        {plan.quarterly_price && (
          <div className="flex justify-between">
            <span className="text-sm text-gray-600">Quarterly:</span>
            <span className="font-semibold">RM {plan.quarterly_price}</span>
          </div>
        )}
        {plan.half_yearly_price && (
          <div className="flex justify-between">
            <span className="text-sm text-gray-600">Half Yearly:</span>
            <span className="font-semibold">RM {plan.half_yearly_price}</span>
          </div>
        )}
        <div className="flex justify-between">
          <span className="text-sm text-gray-600">Yearly:</span>
          <span className="font-semibold">RM {plan.yearly_price}</span>
        </div>
        {plan.commitment_fee > 0 && (
          <div className="flex justify-between">
            <span className="text-sm text-gray-600">Commitment Fee:</span>
            <span className="font-semibold">RM {plan.commitment_fee}</span>
          </div>
        )}
      </div>

      {plan.coverage_details && (
        <div className="mt-4 pt-4 border-t border-gray-200">
          <h4 className="text-sm font-medium text-gray-800 mb-2">Coverage Details:</h4>
          <ul className="text-xs text-gray-600 space-y-1">
            {Object.entries(plan.coverage_details).map(([key, value]) => (
              <li key={key} className="flex justify-between">
                <span className="capitalize">{key.replace(/_/g, ' ')}:</span>
                <span>{value as string}</span>
              </li>
            ))}
          </ul>
        </div>
      )}
    </motion.div>
  );

  const renderPaymentFrequency = () => (
    <div className="space-y-4">
      <h3 className="text-lg font-semibold text-gray-800">Select Payment Frequency</h3>
      <div className="grid grid-cols-2 gap-4">
        {['monthly', 'quarterly', 'half_yearly', 'yearly'].map((frequency) => {
          const freq = frequency as 'monthly' | 'quarterly' | 'half_yearly' | 'yearly';
          const isAvailable = selectedPlan && getPlanPrice(selectedPlan, frequency) > 0;
          
          return (
            <button
              key={frequency}
              disabled={!isAvailable}
              onClick={() => handlePaymentFrequencyChange(freq)}
              className={`p-4 rounded-lg border-2 transition-all duration-200 ${
                paymentFrequency === frequency
                  ? 'border-emerald-500 bg-emerald-50'
                  : isAvailable
                  ? 'border-gray-200 bg-white hover:border-gray-300'
                  : 'border-gray-100 bg-gray-50 cursor-not-allowed opacity-50'
              }`}
            >
              <div className="text-center">
                <div className="font-semibold text-gray-800 capitalize">{frequency.replace('_', ' ')}</div>
                {selectedPlan && isAvailable && (
                  <div className="text-sm text-gray-600">
                    RM {getPlanPrice(selectedPlan, frequency)}
                  </div>
                )}
              </div>
            </button>
          );
        })}
      </div>
    </div>
  );

  const renderPaymentSummary = () => {
    if (!selectedPlan) return null;

    const totalPrice = getTotalPrice(selectedPlan, paymentFrequency);
    const basePrice = getPlanPrice(selectedPlan, paymentFrequency);

    return (
      <div className="bg-gray-50 rounded-xl p-6">
        <h3 className="text-lg font-semibold text-gray-800 mb-4">Payment Summary</h3>
        
        <div className="space-y-3">
          <div className="flex justify-between">
            <span className="text-gray-600">Plan:</span>
            <span className="font-medium">{selectedPlan.name}</span>
          </div>
          
          <div className="flex justify-between">
            <span className="text-gray-600">Frequency:</span>
            <span className="font-medium capitalize">{paymentFrequency.replace('_', ' ')}</span>
          </div>
          
          <div className="flex justify-between">
            <span className="text-gray-600">Base Price:</span>
            <span className="font-medium">RM {basePrice}</span>
          </div>
          
          {selectedPlan.commitment_fee > 0 && (
            <div className="flex justify-between">
              <span className="text-gray-600">Commitment Fee:</span>
              <span className="font-medium">RM {selectedPlan.commitment_fee}</span>
            </div>
          )}
          
          <div className="border-t border-gray-200 pt-3">
            <div className="flex justify-between text-lg font-bold">
              <span>Total Amount:</span>
              <span className="text-emerald-600">RM {totalPrice}</span>
            </div>
          </div>
        </div>
      </div>
    );
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          className="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
        >
          <motion.div
            initial={{ scale: 0.9, opacity: 0, y: 20 }}
            animate={{ scale: 1, opacity: 1, y: 0 }}
            exit={{ scale: 0.9, opacity: 0, y: 20 }}
            transition={{ type: "spring", stiffness: 300, damping: 30 }}
            className="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto relative z-[10000]"
          >
            <div className="flex items-center justify-between p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-800">Medical Insurance Payment</h2>
              <button
                onClick={onClose}
                className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <X className="w-5 h-5 text-gray-600" />
              </button>
            </div>

            <div className="p-6 space-y-6">
              {error && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                  <p className="text-red-800 text-sm">{error}</p>
                </div>
              )}

              <div className="text-center">
                <h3 className="text-lg font-semibold text-gray-800 mb-2">Payment Summary</h3>
                <p className="text-sm text-gray-600">Review your medical insurance registration details before proceeding to payment:</p>
              </div>

              <div className="bg-white border border-gray-200 rounded-lg p-6">
                <h4 className="font-semibold text-gray-800 mb-4">Registration Details</h4>
                <div className="space-y-3">
                  <div className="flex justify-between">
                    <span className="text-gray-600">Registration ID:</span>
                    <span className="font-medium">#{registrationId}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Total Customers:</span>
                    <span className="font-medium">Multiple customers registered</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Payment Method:</span>
                    <span className="font-medium">Curlec (Razorpay)</span>
                  </div>
                </div>
              </div>

              <div className="bg-emerald-50 border border-emerald-200 rounded-lg p-6">
                <h4 className="font-semibold text-emerald-800 mb-4">Payment Information</h4>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-emerald-700">Total Amount:</span>
                    <span className="font-bold text-emerald-800">
                      {totalAmount !== null ? `RM ${totalAmount.toFixed(2)}` : 'Calculating...'}
                    </span>
                  </div>
                  {amountBreakdown.length > 0 && (
                    <div className="mt-2">
                      <div className="text-sm font-semibold text-emerald-900 mb-1">Amount Breakdown</div>
                      <div className="space-y-1 text-sm">
                        {amountBreakdown.map((row, idx) => (
                          <div key={idx} className="flex justify-between text-emerald-800">
                            <span className="truncate">{String(row.customer_type).toUpperCase()} • {row.plan_type} • {String(row.payment_mode).replace('_',' ')}</span>
                            <span>RM {(row.line_total ?? 0).toFixed(2)}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                  <div className="flex justify-between">
                    <span className="text-emerald-700">Currency:</span>
                    <span className="font-medium text-emerald-800">MYR</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-emerald-700">Payment Status:</span>
                    <span className="font-medium text-emerald-800">Ready for payment</span>
                  </div>
                </div>
              </div>

              <div className="flex gap-3 justify-end">
                <button
                  onClick={onClose}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  onClick={handleProceedToPayment}
                  disabled={loading}
                  className="flex items-center gap-2 px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {loading ? (
                    <>
                      <Clock className="w-4 h-4 animate-spin" />
                      Processing...
                    </>
                  ) : (
                    <>
                      <CreditCard className="w-4 h-4" />
                      Proceed to Payment
                    </>
                  )}
                </button>
              </div>
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}

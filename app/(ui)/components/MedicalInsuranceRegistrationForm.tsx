"use client";

import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Plus, Minus, ChevronDown, ChevronUp } from "lucide-react";
import { apiService } from "@/app/services/api";
import MedicalInsurancePaymentPage from "./MedicalInsurancePaymentPage";

interface MedicalInsurancePlan {
  id: number;
  name: string;
  plan_code: string;
  description: string;
  pricing: {
    monthly: { base_price: string };
    quarterly: { base_price: string };
    semi_annually: { base_price: string };
    annually: { base_price: string };
  };
  commitment_fee: string;
  coverage_details: any;
  max_age: number | null;
  min_age: number;
}

interface ClientData {
  plan_type: string;
  full_name: string;
  nric: string;
  race: string;
  height_cm: number;
  weight_kg: number;
  phone_number: string;
  email: string;
  password: string;
  medical_consultation_2_years: boolean;
  serious_illness_history: boolean;
  insurance_rejection_history: boolean;
  serious_injury_history: boolean;
  emergency_contact_name: string;
  emergency_contact_phone: string;
  emergency_contact_relationship: string;
  payment_mode: string;
  medical_card_type: string;
}

interface RegistrationFormData {
  clients: ClientData[];
  plan_id: number;
  payment_mode: string;
}

interface MedicalInsuranceRegistrationFormProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (registration: any) => void;
  externalMode?: boolean;
  agentCode?: string;
}

export default function MedicalInsuranceRegistrationForm({ 
  isOpen, 
  onClose, 
  onSuccess,
  externalMode = false,
  agentCode = ""
}: MedicalInsuranceRegistrationFormProps) {
  const [plans, setPlans] = useState<MedicalInsurancePlan[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [showPaymentPage, setShowPaymentPage] = useState(false);
  const [registrationId, setRegistrationId] = useState<number | null>(null);
  const [formErrors, setFormErrors] = useState<Record<string, string>>({});
  const [clients, setClients] = useState<ClientData[]>([
    {
      plan_type: "",
      full_name: "",
      nric: "",
      race: "Malay",
      height_cm: 0,
      weight_kg: 0,
      phone_number: "",
      email: "",
      password: "",
      medical_consultation_2_years: false,
      serious_illness_history: false,
      insurance_rejection_history: false,
      serious_injury_history: false,
      emergency_contact_name: "",
      emergency_contact_phone: "",
      emergency_contact_relationship: "",
      payment_mode: "monthly",
      medical_card_type: "e-Medical Card",
    }
  ]);

  useEffect(() => {
    if (isOpen) {
      loadPlans();
    }
  }, [isOpen]);

  const loadPlans = async () => {
    try {
      const response = await apiService.getMedicalInsurancePlans();
      if (response.success && response.data && response.data.length > 0) {
        setPlans(response.data);
        // Set default plan for first client
        setClients(prev => prev.map((client, index) => 
          index === 0 ? { ...client, plan_type: response.data![0].name } : client
        ));
      }
    } catch (err) {
      setError("Failed to load medical insurance plans");
    }
  };

  const addClient = () => {
    if (clients.length < 10) {
      setClients(prev => [...prev, {
        plan_type: "",
        full_name: "",
        nric: "",
        race: "Malay",
        height_cm: 0,
        weight_kg: 0,
        phone_number: "",
        email: "",
        password: "",
        medical_consultation_2_years: false,
        serious_illness_history: false,
        insurance_rejection_history: false,
        serious_injury_history: false,
        emergency_contact_name: "",
        emergency_contact_phone: "",
        emergency_contact_relationship: "",
        payment_mode: "monthly",
        medical_card_type: "e-Medical Card",
      }]);
    }
  };

  const removeClient = (index: number) => {
    if (clients.length > 1) {
      setClients(prev => prev.filter((_, i) => i !== index));
    }
  };

  const updateClient = (index: number, field: keyof ClientData, value: any) => {
    setClients(prev => prev.map((client, i) => 
      i === index ? { ...client, [field]: value } : client
    ));
  };

  const normalizeNric = (value: string): string => {
    const digits = (value || '').replace(/[^0-9]/g, '');
    if (digits.length === 12) {
      return `${digits.slice(0, 6)}-${digits.slice(6, 8)}-${digits.slice(8)}`;
    }
    return value;
  };

  const normalizePhone = (value: string): string => {
    let digits = (value || '').replace(/[^0-9]/g, '');
    if (digits.startsWith('60')) {
      digits = digits.slice(2);
    }
    if (!digits.startsWith('01') && digits.startsWith('1')) {
      digits = `0${digits}`;
    }
    if (digits.length > 11) digits = digits.slice(0, 11);
    return digits;
  };

  const toInternationalPhone = (local01: string): string => {
    const local = normalizePhone(local01);
    if (local.startsWith('01')) {
      return `+60${local.slice(1)}`;
    }
    return local ? `+60${local}` : '';
  };

  const getPlanPrice = (planName: string, frequency: string) => {
    const plan = plans.find(p => p.name === planName);
    if (!plan) return 0;

    const toNumber = (v: any) => {
      if (typeof v === 'number') return v;
      const n = parseFloat(v ?? '0');
      return Number.isNaN(n) ? 0 : n;
    };

    switch (frequency) {
      case 'monthly':
        return toNumber(plan.pricing.monthly.base_price) || 0;
      case 'quarterly':
        return toNumber(plan.pricing.quarterly.base_price) || 0;
      case 'semi_annually':
        return toNumber(plan.pricing.semi_annually.base_price) || 0;
      case 'annually':
        return toNumber(plan.pricing.annually.base_price) || 0;
      default:
        return toNumber(plan.pricing.monthly.base_price) || 0;
    }
  };

  const getCommitmentFee = (planName: string, frequency: string) => {
    const plan = plans.find(p => p.name === planName);
    if (!plan || frequency !== 'monthly') return 0;
    
    const toNumber = (v: any) => {
      if (typeof v === 'number') return v;
      const n = parseFloat(v ?? '0');
      return Number.isNaN(n) ? 0 : n;
    };

    return toNumber(plan.commitment_fee) || 0;
  };

  const getNfcCardPrice = (cardType: string) => {
    if (cardType === 'e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)') {
      return 34.90;
    }
    return 0;
  };

  const getClientTotal = (client: ClientData) => {
    const basePrice = getPlanPrice(client.plan_type, client.payment_mode);
    const commitmentFee = getCommitmentFee(client.plan_type, client.payment_mode);
    const nfcPrice = getNfcCardPrice(client.medical_card_type);
    
    return basePrice + commitmentFee + nfcPrice;
  };

  const getGrandTotal = () => {
    return clients.reduce((total, client) => {
      if (client.plan_type) {
        return total + getClientTotal(client);
      }
      return total;
    }, 0);
  };

  const validateClient = (client: ClientData, index: number) => {
    const errs: Record<string, string> = {};
    const phonePattern = /^01\d{8,9}$/;
    
    // Check all required fields
    if (!client.plan_type || client.plan_type.trim() === '') {
      errs[`clients.${index}.plan_type`] = 'Plan type is required.';
    }
    if (!client.full_name || client.full_name.trim() === '') {
      errs[`clients.${index}.full_name`] = 'Full name is required.';
    }
    if (!client.nric || client.nric.trim() === '') {
      errs[`clients.${index}.nric`] = 'NRIC is required.';
    }
    if (!client.race || client.race.trim() === '') {
      errs[`clients.${index}.race`] = 'Race is required.';
    }
    if (!client.height_cm || client.height_cm <= 0) {
      errs[`clients.${index}.height_cm`] = 'Height is required.';
    }
    if (!client.weight_kg || client.weight_kg <= 0) {
      errs[`clients.${index}.weight_kg`] = 'Weight is required.';
    }
    if (!client.email || client.email.trim() === '') {
      errs[`clients.${index}.email`] = 'Email is required.';
    }
    if (!client.password || client.password.trim() === '') {
      errs[`clients.${index}.password`] = 'Password is required.';
    }
    if (!client.emergency_contact_name || client.emergency_contact_name.trim() === '') {
      errs[`clients.${index}.emergency_contact_name`] = 'Emergency contact name is required.';
    }
    if (!client.emergency_contact_relationship || client.emergency_contact_relationship.trim() === '') {
      errs[`clients.${index}.emergency_contact_relationship`] = 'Emergency contact relationship is required.';
    }
    if (!client.medical_card_type || client.medical_card_type.trim() === '') {
      errs[`clients.${index}.medical_card_type`] = 'Medical card type is required.';
    }
    
    const phone = normalizePhone(client.phone_number);
    const ephone = normalizePhone(client.emergency_contact_phone);
    
    // Check if phone number is empty
    if (!client.phone_number || client.phone_number.trim() === '') {
      errs[`clients.${index}.phone_number`] = 'Phone number is required.';
    } else if (!phonePattern.test(phone)) {
      errs[`clients.${index}.phone_number`] = 'Please enter a valid phone number (01XXXXXXXXX).';
    }
    
    if (!client.emergency_contact_phone || client.emergency_contact_phone.trim() === '') {
      errs[`clients.${index}.emergency_contact_phone`] = 'Emergency contact phone number is required.';
    } else if (!phonePattern.test(ephone)) {
      errs[`clients.${index}.emergency_contact_phone`] = 'Please enter a valid emergency contact phone number (01XXXXXXXXX).';
    }
    
    return errs;
  };

  const handleSubmit = async () => {
    console.log('Submit button clicked');
    setLoading(true);
    setError("");
    setFormErrors({});

    try {
      // Validate all clients
      const allErrors: Record<string, string> = {};
      clients.forEach((client, index) => {
        const clientErrors = validateClient(client, index);
        Object.assign(allErrors, clientErrors);
      });

      if (Object.keys(allErrors).length > 0) {
        setFormErrors(allErrors);
        setError('Please correct the fields marked in red.');
        return;
      }

      // Find the plan ID
      const firstClient = clients[0];
      const selectedPlan = plans.find(p => p.name === firstClient.plan_type);
      if (!selectedPlan) {
        setError('Please select a valid plan.');
        return;
      }

      const payload = {
        clients: clients.map(client => ({
          ...client,
          nric: normalizeNric(client.nric).trim(),
          phone_number: normalizePhone(client.phone_number),
          emergency_contact_phone: toInternationalPhone(client.emergency_contact_phone),
          full_name: client.full_name.trim(),
          email: client.email.trim(),
          height_cm: Number(client.height_cm) || 0,
          weight_kg: Number(client.weight_kg) || 0,
        })),
        plan_id: selectedPlan.id,
        payment_mode: firstClient.payment_mode,
        ...(externalMode && agentCode && { agent_code: agentCode }),
      };
      
      console.log('Payload being sent:', payload);

      const response = externalMode 
        ? await apiService.registerMedicalInsuranceExternal(payload)
        : await apiService.registerMedicalInsurance(payload);
        
      if (response.success && response.data && response.data.registration_id) {
        setRegistrationId(response.data.registration_id);
        setShowPaymentPage(true);
      } else {
        if (response.errors) {
          const mapped: Record<string, string> = {};
          Object.keys(response.errors).forEach((key) => {
            const msgs = response.errors![key];
            if (Array.isArray(msgs) && msgs.length > 0) mapped[key] = msgs[0];
          });
          setFormErrors(mapped);
        }
        setError(response.message || 'Registration failed. Please try again.');
      }
    } catch (err: any) {
      setError(err?.message || "Registration failed. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  const handlePaymentSuccess = (payment: any) => {
    if (externalMode) {
      setError("");
      setLoading(true);
    }
    onSuccess(payment);
    if (!externalMode) {
      onClose();
    }
  };

  const handlePaymentClose = () => {
    setShowPaymentPage(false);
    setRegistrationId(null);
  };

  const renderClientForm = (client: ClientData, index: number) => {
    const isFirst = index === 0;
    const clientTotal = getClientTotal(client);
    
    return (
      <div key={index} className="space-y-6 border-t pt-6">
        <div className="flex items-center justify-between">
          <h3 className="text-lg font-semibold text-gray-800">
            {isFirst ? 'Primary Customer' : `Customer ${index + 1}`}
          </h3>
          {!isFirst && (
            <button
              type="button"
              onClick={() => removeClient(index)}
              className="p-2 text-red-600 hover:bg-red-50 rounded-lg"
            >
              <X className="w-5 h-5" />
            </button>
          )}
        </div>

        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Plan Type <span className="text-red-500">*</span>
            </label>
            <select
              value={client.plan_type}
              onChange={(e) => {
                updateClient(index, 'plan_type', e.target.value);
                // Clear error when user selects
                if (formErrors[`clients.${index}.plan_type`]) {
                  setFormErrors(prev => ({
                    ...prev,
                    [`clients.${index}.plan_type`]: ''
                  }));
                }
              }}
              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.plan_type`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
            >
              <option value="">Select Plan</option>
              {plans.map((plan, idx) => (
                <option key={`${plan.id}-${plan.name}-${idx}`} value={plan.name}>
                  {plan.name}
                </option>
              ))}
            </select>
            {formErrors[`clients.${index}.plan_type`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.plan_type`]}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Full Name <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              value={client.full_name}
              onChange={(e) => {
                updateClient(index, 'full_name', e.target.value);
                // Clear error when user starts typing
                if (formErrors[`clients.${index}.full_name`]) {
                  setFormErrors(prev => ({
                    ...prev,
                    [`clients.${index}.full_name`]: ''
                  }));
                }
              }}
              placeholder="Cth: Ridhuan Shah"
              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.full_name`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
            />
            {formErrors[`clients.${index}.full_name`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.full_name`]}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              NRIC Number <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              value={client.nric}
              onChange={(e) => {
                updateClient(index, 'nric', e.target.value);
                // Clear error when user starts typing
                if (formErrors[`clients.${index}.nric`]) {
                  setFormErrors(prev => ({
                    ...prev,
                    [`clients.${index}.nric`]: ''
                  }));
                }
              }}
              placeholder="Cth: 950611-14-7183"
              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.nric`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
            />
            {formErrors[`clients.${index}.nric`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.nric`]}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Race <span className="text-red-500">*</span>
            </label>
            <select
              value={client.race}
              onChange={(e) => {
                updateClient(index, 'race', e.target.value);
                // Clear error when user selects
                if (formErrors[`clients.${index}.race`]) {
                  setFormErrors(prev => ({
                    ...prev,
                    [`clients.${index}.race`]: ''
                  }));
                }
              }}
              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.race`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
            >
              <option value="Malay">Malay</option>
              <option value="Chinese">Chinese</option>
              <option value="Indian">Indian</option>
              <option value="Others">Others</option>
            </select>
            {formErrors[`clients.${index}.race`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.race`]}</p>
            )}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Height (cm) <span className="text-red-500">*</span>
              </label>
              <input
                type="number"
                value={client.height_cm}
                onChange={(e) => {
                  updateClient(index, 'height_cm', parseInt(e.target.value) || 0);
                  // Clear error when user starts typing
                  if (formErrors[`clients.${index}.height_cm`]) {
                    setFormErrors(prev => ({
                      ...prev,
                      [`clients.${index}.height_cm`]: ''
                    }));
                  }
                }}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.height_cm`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
              />
              {formErrors[`clients.${index}.height_cm`] && (
                <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.height_cm`]}</p>
              )}
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Weight (kg) <span className="text-red-500">*</span>
              </label>
              <input
                type="number"
                value={client.weight_kg}
                onChange={(e) => {
                  updateClient(index, 'weight_kg', parseInt(e.target.value) || 0);
                  // Clear error when user starts typing
                  if (formErrors[`clients.${index}.weight_kg`]) {
                    setFormErrors(prev => ({
                      ...prev,
                      [`clients.${index}.weight_kg`]: ''
                    }));
                  }
                }}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.weight_kg`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
              />
              {formErrors[`clients.${index}.weight_kg`] && (
                <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.weight_kg`]}</p>
              )}
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Phone Number <span className="text-red-500">*</span>
            </label>
            <div className={`flex ${formErrors[`clients.${index}.phone_number`] ? 'ring-2 ring-red-400 rounded-lg' : ''}`}>
              <span className="inline-flex items-center px-3 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50 text-gray-600">
                +60
              </span>
              <input
                type="tel"
                value={client.phone_number}
                onChange={(e) => {
                  updateClient(index, 'phone_number', e.target.value);
                  // Clear error when user starts typing
                  if (formErrors[`clients.${index}.phone_number`]) {
                    setFormErrors(prev => ({
                      ...prev,
                      [`clients.${index}.phone_number`]: ''
                    }));
                  }
                }}
                placeholder="01XXXXXXXXX"
                className={`flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.phone_number`] ? 'border-red-400 focus:ring-red-400' : ''}`}
              />
            </div>
            {formErrors[`clients.${index}.phone_number`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.phone_number`]}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Email <span className="text-red-500">*</span>
            </label>
            <input
              type="email"
              value={client.email}
              onChange={(e) => {
                updateClient(index, 'email', e.target.value);
                // Clear error when user starts typing
                if (formErrors[`clients.${index}.email`]) {
                  setFormErrors(prev => ({
                    ...prev,
                    [`clients.${index}.email`]: ''
                  }));
                }
              }}
              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.email`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
            />
            {formErrors[`clients.${index}.email`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.email`]}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Password <span className="text-red-500">*</span>
            </label>
            <input
              type="password"
              value={client.password}
              onChange={(e) => {
                updateClient(index, 'password', e.target.value);
                // Clear error when user starts typing
                if (formErrors[`clients.${index}.password`]) {
                  setFormErrors(prev => ({
                    ...prev,
                    [`clients.${index}.password`]: ''
                  }));
                }
              }}
              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.password`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
            />
            {formErrors[`clients.${index}.password`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.password`]}</p>
            )}
          </div>
        </div>

        {/* Medical History */}
        <div className="space-y-4">
          <h4 className="font-medium text-gray-800">Maklumat Lain - Sila jawab samada Ya atau Tidak sahaja</h4>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Dalam tempoh 2 tahun yang lepas, adakah anda pernah berjumpa pakar, dimasukkan ke hospital, menjalani pembedahan, menjalani ujian diagnostik dengan keputusan yang tidak normal atau dinasihatkan untuk menjalani mana-mana perkara ini pada masa hadapan? <span className="text-red-500">*</span>
            </label>
            <div className="flex gap-4">
              <label className="flex items-center">
                <input
                  type="radio"
                  name={`medical_consultation_2_years_${index}`}
                  checked={client.medical_consultation_2_years}
                  onChange={(e) => updateClient(index, 'medical_consultation_2_years', true)}
                  className="mr-2"
                />
                Ya
              </label>
              <label className="flex items-center">
                <input
                  type="radio"
                  name={`medical_consultation_2_years_${index}`}
                  checked={!client.medical_consultation_2_years}
                  onChange={(e) => updateClient(index, 'medical_consultation_2_years', false)}
                  className="mr-2"
                />
                Tidak
              </label>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Adakah anda pernah menerima diagnosis atau menunjukkan simptom seperti: Kanser atau tumor; Serangan jantung atau sakit dada; Tekanan darah tinggi, strok, atau diabetes; Hepatitis B atau C; HIV atau AIDS; Sebarang gangguan mental atau saraf; Penyalahgunaan alkohol atau dadah; Gangguan hati, paru-paru, buah pinggang, usus, saraf, atau muskuloskeletal; Sebarang penyakit serius yang lain? <span className="text-red-500">*</span>
            </label>
            <div className="flex gap-4">
              <label className="flex items-center">
                <input
                  type="radio"
                  name={`serious_illness_history_${index}`}
                  checked={client.serious_illness_history}
                  onChange={(e) => updateClient(index, 'serious_illness_history', true)}
                  className="mr-2"
                />
                Ya
              </label>
              <label className="flex items-center">
                <input
                  type="radio"
                  name={`serious_illness_history_${index}`}
                  checked={!client.serious_illness_history}
                  onChange={(e) => updateClient(index, 'serious_illness_history', false)}
                  className="mr-2"
                />
                Tidak
              </label>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Adakah permohonan insurans/takaful anda pernah ditolak? <span className="text-red-500">*</span>
            </label>
            <div className="flex gap-4">
              <label className="flex items-center">
                <input
                  type="radio"
                  name={`insurance_rejection_history_${index}`}
                  checked={client.insurance_rejection_history}
                  onChange={(e) => updateClient(index, 'insurance_rejection_history', true)}
                  className="mr-2"
                />
                Ya
              </label>
              <label className="flex items-center">
                <input
                  type="radio"
                  name={`insurance_rejection_history_${index}`}
                  checked={!client.insurance_rejection_history}
                  onChange={(e) => updateClient(index, 'insurance_rejection_history', false)}
                  className="mr-2"
                />
                Tidak
              </label>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Adakah anda pernah mengalami kecederaan serius (tidak termasuk luka kecil, lebam, calar, dan gigitan serangga) yang memerlukan kemasukan ke hospital atau tempoh pemulihan yang lama di rumah? <span className="text-red-500">*</span>
            </label>
            <div className="flex gap-4">
              <label className="flex items-center">
                <input
                  type="radio"
                  name={`serious_injury_history_${index}`}
                  checked={client.serious_injury_history}
                  onChange={(e) => updateClient(index, 'serious_injury_history', true)}
                  className="mr-2"
                />
                Ya
              </label>
              <label className="flex items-center">
                <input
                  type="radio"
                  name={`serious_injury_history_${index}`}
                  checked={!client.serious_injury_history}
                  onChange={(e) => updateClient(index, 'serious_injury_history', false)}
                  className="mr-2"
                />
                Tidak
              </label>
            </div>
          </div>
        </div>

        {/* Emergency Contact */}
        <div className="space-y-4">
          <h4 className="font-medium text-gray-800">Emergency Contact</h4>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Nama Per IC <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              value={client.emergency_contact_name}
              onChange={(e) => {
                updateClient(index, 'emergency_contact_name', e.target.value);
                // Clear error when user starts typing
                if (formErrors[`clients.${index}.emergency_contact_name`]) {
                  setFormErrors(prev => ({
                    ...prev,
                    [`clients.${index}.emergency_contact_name`]: ''
                  }));
                }
              }}
              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.emergency_contact_name`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
            />
            {formErrors[`clients.${index}.emergency_contact_name`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.emergency_contact_name`]}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Contact No <span className="text-red-500">*</span>
            </label>
            <div className={`flex ${formErrors[`clients.${index}.emergency_contact_phone`] ? 'ring-2 ring-red-400 rounded-lg' : ''}`}>
              <span className="inline-flex items-center px-3 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50 text-gray-600">
                +60
              </span>
              <input
                type="tel"
                value={client.emergency_contact_phone}
                onChange={(e) => updateClient(index, 'emergency_contact_phone', e.target.value)}
                placeholder="01XXXXXXXXX"
                className={`flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.emergency_contact_phone`] ? 'border-red-400 focus:ring-red-400' : ''}`}
              />
            </div>
            {formErrors[`clients.${index}.emergency_contact_phone`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.emergency_contact_phone`]}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Relationship <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              value={client.emergency_contact_relationship}
              onChange={(e) => {
                updateClient(index, 'emergency_contact_relationship', e.target.value);
                // Clear error when user starts typing
                if (formErrors[`clients.${index}.emergency_contact_relationship`]) {
                  setFormErrors(prev => ({
                    ...prev,
                    [`clients.${index}.emergency_contact_relationship`]: ''
                  }));
                }
              }}
              placeholder="Sila pastikan maklumat ini adalah selain dari penama di atas"
              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors[`clients.${index}.emergency_contact_relationship`] ? 'border-red-400 focus:ring-red-400' : 'border-gray-300'}`}
            />
            {formErrors[`clients.${index}.emergency_contact_relationship`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.emergency_contact_relationship`]}</p>
            )}
          </div>
        </div>

        {/* Payment Section */}
        <div className="space-y-4">
          <h4 className="font-medium text-gray-800">Payment Mode</h4>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Contribution Amount ({client.plan_type}): <span className="text-red-500">*</span>
            </label>
            <div className="space-y-2">
              <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <span>Monthly (RM{getPlanPrice(client.plan_type, 'monthly')}{getCommitmentFee(client.plan_type, 'monthly') > 0 ? ` + Commitment Fee RM${getCommitmentFee(client.plan_type, 'monthly')}` : ''})</span>
                <input
                  type="radio"
                  name={`payment_mode_${index}`}
                  value="monthly"
                  checked={client.payment_mode === 'monthly'}
                  onChange={(e) => updateClient(index, 'payment_mode', e.target.value)}
                  className="mr-2"
                />
              </label>
              <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <span>Quarterly (RM{getPlanPrice(client.plan_type, 'quarterly')})</span>
                <input
                  type="radio"
                  name={`payment_mode_${index}`}
                  value="quarterly"
                  checked={client.payment_mode === 'quarterly'}
                  onChange={(e) => updateClient(index, 'payment_mode', e.target.value)}
                  className="mr-2"
                />
              </label>
              <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <span>Half Yearly (RM{getPlanPrice(client.plan_type, 'semi_annually')})</span>
                <input
                  type="radio"
                  name={`payment_mode_${index}`}
                  value="semi_annually"
                  checked={client.payment_mode === 'semi_annually'}
                  onChange={(e) => updateClient(index, 'payment_mode', e.target.value)}
                  className="mr-2"
                />
              </label>
              <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <span>Yearly (RM{getPlanPrice(client.plan_type, 'annually')})</span>
                <input
                  type="radio"
                  name={`payment_mode_${index}`}
                  value="annually"
                  checked={client.payment_mode === 'annually'}
                  onChange={(e) => updateClient(index, 'payment_mode', e.target.value)}
                  className="mr-2"
                />
              </label>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Medical Card Type <span className="text-red-500">*</span>
            </label>
            <div className={`space-y-2 ${formErrors[`clients.${index}.medical_card_type`] ? 'ring-2 ring-red-400 rounded-lg p-2' : ''}`}>
              <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <span>e-Medical Card</span>
                <input
                  type="radio"
                  name={`medical_card_type_${index}`}
                  value="e-Medical Card"
                  checked={client.medical_card_type === 'e-Medical Card'}
                  onChange={(e) => {
                    updateClient(index, 'medical_card_type', e.target.value);
                    // Clear error when user selects
                    if (formErrors[`clients.${index}.medical_card_type`]) {
                      setFormErrors(prev => ({
                        ...prev,
                        [`clients.${index}.medical_card_type`]: ''
                      }));
                    }
                  }}
                  className="mr-2"
                />
              </label>
              <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <span>e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)</span>
                <input
                  type="radio"
                  name={`medical_card_type_${index}`}
                  value="e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)"
                  checked={client.medical_card_type === 'e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)'}
                  onChange={(e) => {
                    updateClient(index, 'medical_card_type', e.target.value);
                    // Clear error when user selects
                    if (formErrors[`clients.${index}.medical_card_type`]) {
                      setFormErrors(prev => ({
                        ...prev,
                        [`clients.${index}.medical_card_type`]: ''
                      }));
                    }
                  }}
                  className="mr-2"
                />
              </label>
            </div>
            {formErrors[`clients.${index}.medical_card_type`] && (
              <p className="mt-1 text-xs text-red-600">{formErrors[`clients.${index}.medical_card_type`]}</p>
            )}
          </div>

          <div className="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
            <div className="flex justify-between items-center">
              <span className="font-medium text-emerald-800">Total Amount:</span>
              <span className="text-lg font-bold text-emerald-800">RM {clientTotal.toFixed(2)}</span>
            </div>
          </div>
        </div>
      </div>
    );
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div key="registration-modal"
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
            className="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto relative z-[10000]"
          >
            <div className="flex items-center justify-between p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-800">Medical Insurance Registration</h2>
              {!externalMode && (
                <button
                  onClick={onClose}
                  className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  <X className="w-5 h-5 text-gray-600" />
                </button>
              )}
            </div>

            <div className="p-6 space-y-6" key="registration-content">
              {error && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                  <p className="text-red-800 text-sm">{error}</p>
                </div>
              )}

              <div className="text-center">
                <h3 className="text-lg font-semibold text-gray-800 mb-2">Registration Form</h3>
                <p className="text-sm text-gray-600">MediPlan Coop</p>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Agent Code
                </label>
                <input
                  type="text"
                  value={externalMode ? agentCode : "AGT00001"}
                  disabled
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                />
              </div>

              {clients.map((client, index) => renderClientForm(client, index))}

              {clients.length < 10 && (
                <div className="flex justify-center">
                  <button
                    type="button"
                    onClick={addClient}
                    className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Another Customer
                  </button>
                </div>
              )}

              {/* Grand Total */}
              <div className="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div className="flex justify-between items-center">
                  <span className="text-lg font-bold text-blue-800">Grand Total:</span>
                  <span className="text-2xl font-bold text-blue-800">RM {getGrandTotal().toFixed(2)}</span>
                </div>
              </div>
            </div>

            <div className="p-6 border-t border-gray-200">
              <div className="flex gap-3 justify-end">
                {!externalMode && (
                  <button
                    onClick={onClose}
                    className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    Cancel
                  </button>
                )}
                <button
                  onClick={handleSubmit}
                  disabled={loading}
                  className="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {loading ? 'Submitting...' : 'Submit Registration'}
                </button>
              </div>
            </div>
          </motion.div>
        </motion.div>
      )}

      {registrationId && (
        <MedicalInsurancePaymentPage
          registrationId={registrationId}
          isOpen={showPaymentPage}
          onClose={handlePaymentClose}
          onSuccess={handlePaymentSuccess}
          initialTotalAmount={getGrandTotal()}
          initialBreakdown={[]}
          externalMode={externalMode}
        />
      )}
    </AnimatePresence>
  );
}
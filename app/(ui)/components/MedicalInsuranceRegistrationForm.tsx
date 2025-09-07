"use client";

import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Plus, Minus, ChevronDown, ChevronUp } from "lucide-react";
import { apiService } from "../../services/api";
import MedicalInsurancePaymentPage from "./MedicalInsurancePaymentPage";

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

interface RegistrationFormData {
  // Primary customer
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
  add_second_customer: boolean;
  add_third_customer: boolean;
  add_fourth_customer: boolean;
  add_fifth_customer: boolean;
  add_sixth_customer: boolean;
  add_seventh_customer: boolean;
  add_eighth_customer: boolean;
  add_ninth_customer: boolean;
  add_tenth_customer: boolean;
  
  // Second customer
  second_customer_plan_type: string;
  second_customer_full_name: string;
  second_customer_nric: string;
  second_customer_race: string;
  second_customer_height_cm: number;
  second_customer_weight_kg: number;
  second_customer_phone_number: string;
  second_customer_medical_consultation_2_years: boolean;
  second_customer_serious_illness_history: boolean;
  second_customer_insurance_rejection_history: boolean;
  second_customer_serious_injury_history: boolean;
  second_customer_payment_mode: string;
  second_customer_medical_card_type: string;
  second_customer_emergency_contact_name: string;
  second_customer_emergency_contact_phone: string;
  second_customer_emergency_contact_relationship: string;
  
  // Third customer
  third_customer_plan_type: string;
  third_customer_full_name: string;
  third_customer_nric: string;
  third_customer_race: string;
  third_customer_height_cm: number;
  third_customer_weight_kg: number;
  third_customer_phone_number: string;
  third_customer_medical_consultation_2_years: boolean;
  third_customer_serious_illness_history: boolean;
  third_customer_insurance_rejection_history: boolean;
  third_customer_serious_injury_history: boolean;
  third_customer_payment_mode: string;
  third_customer_medical_card_type: string;
  third_customer_emergency_contact_name: string;
  third_customer_emergency_contact_phone: string;
  third_customer_emergency_contact_relationship: string;
  
  // Fourth customer
  fourth_customer_plan_type: string;
  fourth_customer_full_name: string;
  fourth_customer_nric: string;
  fourth_customer_race: string;
  fourth_customer_height_cm: number;
  fourth_customer_weight_kg: number;
  fourth_customer_phone_number: string;
  fourth_customer_medical_consultation_2_years: boolean;
  fourth_customer_serious_illness_history: boolean;
  fourth_customer_insurance_rejection_history: boolean;
  fourth_customer_serious_injury_history: boolean;
  fourth_customer_payment_mode: string;
  fourth_customer_medical_card_type: string;
  fourth_customer_emergency_contact_name: string;
  fourth_customer_emergency_contact_phone: string;
  fourth_customer_emergency_contact_relationship: string;
  
  // Fifth customer
  fifth_customer_plan_type: string;
  fifth_customer_full_name: string;
  fifth_customer_nric: string;
  fifth_customer_race: string;
  fifth_customer_height_cm: number;
  fifth_customer_weight_kg: number;
  fifth_customer_phone_number: string;
  fifth_customer_medical_consultation_2_years: boolean;
  fifth_customer_serious_illness_history: boolean;
  fifth_customer_insurance_rejection_history: boolean;
  fifth_customer_serious_injury_history: boolean;
  fifth_customer_payment_mode: string;
  fifth_customer_medical_card_type: string;
  fifth_customer_emergency_contact_name: string;
  fifth_customer_emergency_contact_phone: string;
  fifth_customer_emergency_contact_relationship: string;
  
  // Sixth customer
  sixth_customer_plan_type: string;
  sixth_customer_full_name: string;
  sixth_customer_nric: string;
  sixth_customer_race: string;
  sixth_customer_height_cm: number;
  sixth_customer_weight_kg: number;
  sixth_customer_phone_number: string;
  sixth_customer_medical_consultation_2_years: boolean;
  sixth_customer_serious_illness_history: boolean;
  sixth_customer_insurance_rejection_history: boolean;
  sixth_customer_serious_injury_history: boolean;
  sixth_customer_payment_mode: string;
  sixth_customer_medical_card_type: string;
  sixth_customer_emergency_contact_name: string;
  sixth_customer_emergency_contact_phone: string;
  sixth_customer_emergency_contact_relationship: string;
  
  // Seventh customer
  seventh_customer_plan_type: string;
  seventh_customer_full_name: string;
  seventh_customer_nric: string;
  seventh_customer_race: string;
  seventh_customer_height_cm: number;
  seventh_customer_weight_kg: number;
  seventh_customer_phone_number: string;
  seventh_customer_medical_consultation_2_years: boolean;
  seventh_customer_serious_illness_history: boolean;
  seventh_customer_insurance_rejection_history: boolean;
  seventh_customer_serious_injury_history: boolean;
  seventh_customer_payment_mode: string;
  seventh_customer_medical_card_type: string;
  seventh_customer_emergency_contact_name: string;
  seventh_customer_emergency_contact_phone: string;
  seventh_customer_emergency_contact_relationship: string;
  
  // Eighth customer
  eighth_customer_plan_type: string;
  eighth_customer_full_name: string;
  eighth_customer_nric: string;
  eighth_customer_race: string;
  eighth_customer_height_cm: number;
  eighth_customer_weight_kg: number;
  eighth_customer_phone_number: string;
  eighth_customer_medical_consultation_2_years: boolean;
  eighth_customer_serious_illness_history: boolean;
  eighth_customer_insurance_rejection_history: boolean;
  eighth_customer_serious_injury_history: boolean;
  eighth_customer_payment_mode: string;
  eighth_customer_medical_card_type: string;
  eighth_customer_emergency_contact_name: string;
  eighth_customer_emergency_contact_phone: string;
  eighth_customer_emergency_contact_relationship: string;
  
  // Ninth customer
  ninth_customer_plan_type: string;
  ninth_customer_full_name: string;
  ninth_customer_nric: string;
  ninth_customer_race: string;
  ninth_customer_height_cm: number;
  ninth_customer_weight_kg: number;
  ninth_customer_phone_number: string;
  ninth_customer_medical_consultation_2_years: boolean;
  ninth_customer_serious_illness_history: boolean;
  ninth_customer_insurance_rejection_history: boolean;
  ninth_customer_serious_injury_history: boolean;
  ninth_customer_payment_mode: string;
  ninth_customer_medical_card_type: string;
  ninth_customer_emergency_contact_name: string;
  ninth_customer_emergency_contact_phone: string;
  ninth_customer_emergency_contact_relationship: string;
  
  // Tenth customer
  tenth_customer_plan_type: string;
  tenth_customer_full_name: string;
  tenth_customer_nric: string;
  tenth_customer_race: string;
  tenth_customer_height_cm: number;
  tenth_customer_weight_kg: number;
  tenth_customer_phone_number: string;
  tenth_customer_medical_consultation_2_years: boolean;
  tenth_customer_serious_illness_history: boolean;
  tenth_customer_insurance_rejection_history: boolean;
  tenth_customer_serious_injury_history: boolean;
  tenth_customer_payment_mode: string;
  tenth_customer_medical_card_type: string;
  tenth_customer_emergency_contact_name: string;
  tenth_customer_emergency_contact_phone: string;
  tenth_customer_emergency_contact_relationship: string;
}

interface MedicalInsuranceRegistrationFormProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (registration: any) => void;
}

export default function MedicalInsuranceRegistrationForm({ 
  isOpen, 
  onClose, 
  onSuccess 
}: MedicalInsuranceRegistrationFormProps) {
  const [plans, setPlans] = useState<MedicalInsurancePlan[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [currentStep, setCurrentStep] = useState(1);
  const [showSecondCustomer, setShowSecondCustomer] = useState(false);
  const [showThirdCustomer, setShowThirdCustomer] = useState(false);
  const [showFourthCustomer, setShowFourthCustomer] = useState(false);
  const [showFifthCustomer, setShowFifthCustomer] = useState(false);
  const [showSixthCustomer, setShowSixthCustomer] = useState(false);
  const [showSeventhCustomer, setShowSeventhCustomer] = useState(false);
  const [showEighthCustomer, setShowEighthCustomer] = useState(false);
  const [showNinthCustomer, setShowNinthCustomer] = useState(false);
  const [showTenthCustomer, setShowTenthCustomer] = useState(false);
  const [showPaymentPage, setShowPaymentPage] = useState(false);
  const [registrationId, setRegistrationId] = useState<number | null>(null);
  const [formErrors, setFormErrors] = useState<Record<string, string>>({});

  const [formData, setFormData] = useState<RegistrationFormData>({
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
    add_second_customer: false,
    add_third_customer: false,
    add_fourth_customer: false,
    add_fifth_customer: false,
    add_sixth_customer: false,
    add_seventh_customer: false,
    add_eighth_customer: false,
    add_ninth_customer: false,
    add_tenth_customer: false,
    
    // Second customer
    second_customer_plan_type: "",
    second_customer_full_name: "",
    second_customer_nric: "",
    second_customer_race: "Malay",
    second_customer_height_cm: 0,
    second_customer_weight_kg: 0,
    second_customer_phone_number: "",
    second_customer_medical_consultation_2_years: false,
    second_customer_serious_illness_history: false,
    second_customer_insurance_rejection_history: false,
    second_customer_serious_injury_history: false,
    second_customer_payment_mode: "monthly",
    second_customer_medical_card_type: "e-Medical Card",
    second_customer_emergency_contact_name: "",
    second_customer_emergency_contact_phone: "",
    second_customer_emergency_contact_relationship: "",
    
    // Third customer
    third_customer_plan_type: "",
    third_customer_full_name: "",
    third_customer_nric: "",
    third_customer_race: "Malay",
    third_customer_height_cm: 0,
    third_customer_weight_kg: 0,
    third_customer_phone_number: "",
    third_customer_medical_consultation_2_years: false,
    third_customer_serious_illness_history: false,
    third_customer_insurance_rejection_history: false,
    third_customer_serious_injury_history: false,
    third_customer_payment_mode: "monthly",
    third_customer_medical_card_type: "e-Medical Card",
    third_customer_emergency_contact_name: "",
    third_customer_emergency_contact_phone: "",
    third_customer_emergency_contact_relationship: "",
    
    // Fourth customer
    fourth_customer_plan_type: "",
    fourth_customer_full_name: "",
    fourth_customer_nric: "",
    fourth_customer_race: "Malay",
    fourth_customer_height_cm: 0,
    fourth_customer_weight_kg: 0,
    fourth_customer_phone_number: "",
    fourth_customer_medical_consultation_2_years: false,
    fourth_customer_serious_illness_history: false,
    fourth_customer_insurance_rejection_history: false,
    fourth_customer_serious_injury_history: false,
    fourth_customer_payment_mode: "monthly",
    fourth_customer_medical_card_type: "e-Medical Card",
    fourth_customer_emergency_contact_name: "",
    fourth_customer_emergency_contact_phone: "",
    fourth_customer_emergency_contact_relationship: "",
    
    // Fifth customer
    fifth_customer_plan_type: "",
    fifth_customer_full_name: "",
    fifth_customer_nric: "",
    fifth_customer_race: "Malay",
    fifth_customer_height_cm: 0,
    fifth_customer_weight_kg: 0,
    fifth_customer_phone_number: "",
    fifth_customer_medical_consultation_2_years: false,
    fifth_customer_serious_illness_history: false,
    fifth_customer_insurance_rejection_history: false,
    fifth_customer_serious_injury_history: false,
    fifth_customer_payment_mode: "monthly",
    fifth_customer_medical_card_type: "e-Medical Card",
    fifth_customer_emergency_contact_name: "",
    fifth_customer_emergency_contact_phone: "",
    fifth_customer_emergency_contact_relationship: "",
    
    // Sixth customer
    sixth_customer_plan_type: "",
    sixth_customer_full_name: "",
    sixth_customer_nric: "",
    sixth_customer_race: "Malay",
    sixth_customer_height_cm: 0,
    sixth_customer_weight_kg: 0,
    sixth_customer_phone_number: "",
    sixth_customer_medical_consultation_2_years: false,
    sixth_customer_serious_illness_history: false,
    sixth_customer_insurance_rejection_history: false,
    sixth_customer_serious_injury_history: false,
    sixth_customer_payment_mode: "monthly",
    sixth_customer_medical_card_type: "e-Medical Card",
    sixth_customer_emergency_contact_name: "",
    sixth_customer_emergency_contact_phone: "",
    sixth_customer_emergency_contact_relationship: "",
    
    // Seventh customer
    seventh_customer_plan_type: "",
    seventh_customer_full_name: "",
    seventh_customer_nric: "",
    seventh_customer_race: "Malay",
    seventh_customer_height_cm: 0,
    seventh_customer_weight_kg: 0,
    seventh_customer_phone_number: "",
    seventh_customer_medical_consultation_2_years: false,
    seventh_customer_serious_illness_history: false,
    seventh_customer_insurance_rejection_history: false,
    seventh_customer_serious_injury_history: false,
    seventh_customer_payment_mode: "monthly",
    seventh_customer_medical_card_type: "e-Medical Card",
    seventh_customer_emergency_contact_name: "",
    seventh_customer_emergency_contact_phone: "",
    seventh_customer_emergency_contact_relationship: "",
    
    // Eighth customer
    eighth_customer_plan_type: "",
    eighth_customer_full_name: "",
    eighth_customer_nric: "",
    eighth_customer_race: "Malay",
    eighth_customer_height_cm: 0,
    eighth_customer_weight_kg: 0,
    eighth_customer_phone_number: "",
    eighth_customer_medical_consultation_2_years: false,
    eighth_customer_serious_illness_history: false,
    eighth_customer_insurance_rejection_history: false,
    eighth_customer_serious_injury_history: false,
    eighth_customer_payment_mode: "monthly",
    eighth_customer_medical_card_type: "e-Medical Card",
    eighth_customer_emergency_contact_name: "",
    eighth_customer_emergency_contact_phone: "",
    eighth_customer_emergency_contact_relationship: "",
    
    // Ninth customer
    ninth_customer_plan_type: "",
    ninth_customer_full_name: "",
    ninth_customer_nric: "",
    ninth_customer_race: "Malay",
    ninth_customer_height_cm: 0,
    ninth_customer_weight_kg: 0,
    ninth_customer_phone_number: "",
    ninth_customer_medical_consultation_2_years: false,
    ninth_customer_serious_illness_history: false,
    ninth_customer_insurance_rejection_history: false,
    ninth_customer_serious_injury_history: false,
    ninth_customer_payment_mode: "monthly",
    ninth_customer_medical_card_type: "e-Medical Card",
    ninth_customer_emergency_contact_name: "",
    ninth_customer_emergency_contact_phone: "",
    ninth_customer_emergency_contact_relationship: "",
    
    // Tenth customer
    tenth_customer_plan_type: "",
    tenth_customer_full_name: "",
    tenth_customer_nric: "",
    tenth_customer_race: "Malay",
    tenth_customer_height_cm: 0,
    tenth_customer_weight_kg: 0,
    tenth_customer_phone_number: "",
    tenth_customer_medical_consultation_2_years: false,
    tenth_customer_serious_illness_history: false,
    tenth_customer_insurance_rejection_history: false,
    tenth_customer_serious_injury_history: false,
    tenth_customer_payment_mode: "monthly",
    tenth_customer_medical_card_type: "e-Medical Card",
    tenth_customer_emergency_contact_name: "",
    tenth_customer_emergency_contact_phone: "",
    tenth_customer_emergency_contact_relationship: "",
  });

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
        // Set default plan
        setFormData(prev => ({ ...prev, plan_type: response.data![0].name }));
      }
    } catch (err) {
      setError("Failed to load medical insurance plans");
    }
  };

  const handleInputChange = (field: keyof RegistrationFormData, value: any) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleSecondCustomerChange = (field: keyof RegistrationFormData, value: any) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleThirdCustomerChange = (field: keyof RegistrationFormData, value: any) => {
    setFormData(prev => ({ ...prev, [field]: value }));
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
    // keep only up to 11 digits to avoid accidental long numbers
    if (digits.length > 11) digits = digits.slice(0, 11);
    return digits;
  };

  const toInternationalPhone = (local01: string): string => {
    const local = normalizePhone(local01);
    if (local.startsWith('01')) {
      return `+60${local.slice(1)}`; // 01XXXXXXXXX -> +601XXXXXXXXX
    }
    return local ? `+60${local}` : '';
  };

  const extractFirstErrorMessage = (errors: any): string => {
    if (!errors) return 'Validation failed. Please review your input.';
    const firstKey = Object.keys(errors)[0];
    const messages = errors[firstKey];
    if (Array.isArray(messages) && messages.length > 0) return messages[0];
    if (typeof messages === 'string') return messages;
    return 'Validation failed. Please review your input.';
  };

  const validateClient = () => {
    const errs: Record<string, string> = {};
    const phonePattern = /^01\d{8,9}$/;
    
    // Validate primary customer phone numbers
    const phone = normalizePhone(formData.phone_number);
    const ephone = normalizePhone(formData.emergency_contact_phone);
    if (!phonePattern.test(phone)) {
      errs.phone_number = 'Please enter a valid phone number (01XXXXXXXXX).';
    }
    if (!phonePattern.test(ephone)) {
      errs.emergency_contact_phone = 'Please enter a valid emergency contact phone number (01XXXXXXXXX).';
    }
    
    // Validate additional customers' phone numbers
    const customerNumbers = ['second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth'];
    
    customerNumbers.forEach(customerNumber => {
      const phoneField = `${customerNumber}_customer_phone_number`;
      const emergencyPhoneField = `${customerNumber}_customer_emergency_contact_phone`;
      
      // Only validate if the customer section is visible and has data
      const isVisible = customerNumber === 'second' ? showSecondCustomer :
                       customerNumber === 'third' ? showThirdCustomer :
                       customerNumber === 'fourth' ? showFourthCustomer :
                       customerNumber === 'fifth' ? showFifthCustomer :
                       customerNumber === 'sixth' ? showSixthCustomer :
                       customerNumber === 'seventh' ? showSeventhCustomer :
                       customerNumber === 'eighth' ? showEighthCustomer :
                       customerNumber === 'ninth' ? showNinthCustomer :
                       showTenthCustomer;
      
      if (isVisible) {
        const customerPhone = normalizePhone(formData[phoneField as keyof RegistrationFormData] as string);
        const customerEmergencyPhone = normalizePhone(formData[emergencyPhoneField as keyof RegistrationFormData] as string);
        
        if (customerPhone && !phonePattern.test(customerPhone)) {
          errs[phoneField] = `Please enter a valid phone number for ${customerNumber} customer (01XXXXXXXXX).`;
        }
        if (customerEmergencyPhone && !phonePattern.test(customerEmergencyPhone)) {
          errs[emergencyPhoneField] = `Please enter a valid emergency contact phone number for ${customerNumber} customer (01XXXXXXXXX).`;
        }
      }
    });
    
    return errs;
  };

  const handlePhoneChange = (value: string) => {
    const local = normalizePhone(value);
    setFormData(prev => ({
      ...prev,
      phone_number: local,
    }));
    // Clear per-field errors when editing
    setFormErrors(prev => ({
      ...prev,
      phone_number: '',
    }));
  };

  const handleEmergencyPhoneChange = (value: string) => {
    const local = normalizePhone(value);
    setFormData(prev => ({
      ...prev,
      emergency_contact_phone: local,
    }));
    // Clear per-field errors when editing
    setFormErrors(prev => ({
      ...prev,
      emergency_contact_phone: '',
    }));
  };

  const handleCustomerPhoneChange = (field: string, value: string) => {
    const local = normalizePhone(value);
    setFormData(prev => ({
      ...prev,
      [field]: local,
    }));
    // Clear per-field errors when editing
    setFormErrors(prev => ({
      ...prev,
      [field]: '',
    }));
  };

  const handleSubmit = async () => {
    console.log('Submit button clicked');
    setLoading(true);
    setError("");
    setFormErrors({});

    try {
      const clientErrors = validateClient();
      console.log('Validation errors:', clientErrors);
      if (Object.keys(clientErrors).length > 0) {
        setFormErrors(clientErrors);
        setError('Please correct the fields marked in red.');
        return;
      }

      const payload = {
        ...formData,
        nric: normalizeNric(formData.nric).trim(),
        phone_number: normalizePhone(formData.phone_number),
        emergency_contact_phone: toInternationalPhone(formData.emergency_contact_phone),
        full_name: (formData.full_name || '').trim(),
        email: (formData.email || '').trim(),
        height_cm: Number(formData.height_cm) || 0,
        weight_kg: Number(formData.weight_kg) || 0,
      };
      
      console.log('Payload being sent:', payload);

      const response = await apiService.registerMedicalInsurance(payload);
      if (response.success && response.data && response.data.id) {
        setRegistrationId(response.data.id);
        setShowPaymentPage(true);
      } else {
        // Map backend field errors to inline errors
        if (response.errors) {
          const mapped: Record<string, string> = {};
          Object.keys(response.errors).forEach((key) => {
            const msgs = response.errors![key];
            if (Array.isArray(msgs) && msgs.length > 0) mapped[key] = msgs[0];
          });
          setFormErrors(mapped);
        }
        setError(response.message || extractFirstErrorMessage(response.errors));
      }
    } catch (err: any) {
      setError(err?.message || "Registration failed. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  const handlePaymentSuccess = (payment: any) => {
    onSuccess(payment);
    onClose();
  };

  const handlePaymentClose = () => {
    setShowPaymentPage(false);
    setRegistrationId(null);
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
        return toNumber(plan.monthly_price) || 0;
      case 'quarterly':
        return toNumber(plan.quarterly_price) || 0;
      case 'half_yearly':
        return toNumber(plan.half_yearly_price) || 0;
      case 'yearly':
        return toNumber(plan.yearly_price) || 0;
      default:
        return toNumber(plan.monthly_price) || 0;
    }
  };

  const getNfcCardPrice = (cardType: string) => {
    if (cardType === 'e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)') {
      return 34.90;
    }
    return 0;
  };

  const getTotalPrice = (planName: string, frequency: string, cardType: string = 'e-Medical Card') => {
    const plan = plans.find(p => p.name === planName);
    if (!plan) return 0;

    const basePrice = getPlanPrice(planName, frequency);
    const commitment = typeof (plan as any).commitment_fee === 'number'
      ? (plan as any).commitment_fee
      : parseFloat(((plan as any).commitment_fee ?? '0') as string);
    const nfcPrice = getNfcCardPrice(cardType);
    
    return (Number.isNaN(basePrice) ? 0 : basePrice) + 
           (Number.isNaN(commitment) ? 0 : commitment) + 
           nfcPrice;
  };
  
  const calculateGrandTotal = () => {
    let total = 0;
    
    // Primary customer
    if (formData.plan_type) {
      total += getTotalPrice(
        formData.plan_type, 
        formData.payment_mode, 
        formData.medical_card_type
      );
    }
    
    // Second customer
    if (showSecondCustomer && formData.second_customer_plan_type) {
      total += getTotalPrice(
        formData.second_customer_plan_type, 
        formData.second_customer_payment_mode, 
        formData.second_customer_medical_card_type
      );
    }
    
    // Third customer
    if (showThirdCustomer && formData.third_customer_plan_type) {
      total += getTotalPrice(
        formData.third_customer_plan_type, 
        formData.third_customer_payment_mode, 
        formData.third_customer_medical_card_type
      );
    }
    
    // Fourth to tenth customers
    const additionalCustomers = [
      { visible: showFourthCustomer, prefix: 'fourth_customer' },
      { visible: showFifthCustomer, prefix: 'fifth_customer' },
      { visible: showSixthCustomer, prefix: 'sixth_customer' },
      { visible: showSeventhCustomer, prefix: 'seventh_customer' },
      { visible: showEighthCustomer, prefix: 'eighth_customer' },
      { visible: showNinthCustomer, prefix: 'ninth_customer' },
      { visible: showTenthCustomer, prefix: 'tenth_customer' },
    ];
    
    additionalCustomers.forEach(({ visible, prefix }) => {
      const planField = `${prefix}_plan_type` as keyof RegistrationFormData;
      const paymentField = `${prefix}_payment_mode` as keyof RegistrationFormData;
      const cardField = `${prefix}_medical_card_type` as keyof RegistrationFormData;
      
      if (visible && formData[planField]) {
        total += getTotalPrice(
          formData[planField] as string,
          formData[paymentField] as string,
          formData[cardField] as string
        );
      }
    });
    
    return total;
  };

  const renderStep1 = () => (
    <div className="space-y-6">
      <div className="text-center">
        <h3 className="text-lg font-semibold text-gray-800 mb-2">Registration Form</h3>
        <p className="text-sm text-gray-600">MediPlan Coop</p>
      </div>

      <div className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Agent Code
          </label>
          <input
            type="text"
            value="AGT00001"
            disabled
            className="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Plan Type <span className="text-red-500">*</span>
          </label>
          <select
            value={formData.plan_type}
            onChange={(e) => handleInputChange('plan_type', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
          >
            <option value="">Select Plan</option>
            {plans.map((plan, idx) => (
              <option key={`${plan.id}-${plan.name}-${idx}`} value={plan.name}>
                {plan.name}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Full Name <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={formData.full_name}
            onChange={(e) => handleInputChange('full_name', e.target.value)}
            placeholder="Cth: Ridhuan Shah"
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            NRIC Number <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={formData.nric}
            onChange={(e) => handleInputChange('nric', e.target.value)}
            placeholder="Cth: 950611-14-7183"
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Race <span className="text-red-500">*</span>
          </label>
          <select
            value={formData.race}
            onChange={(e) => handleInputChange('race', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
          >
            <option value="Malay">Malay</option>
            <option value="Chinese">Chinese</option>
            <option value="Indian">Indian</option>
            <option value="Others">Others</option>
          </select>
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Height (cm) <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              value={formData.height_cm}
              onChange={(e) => handleInputChange('height_cm', parseInt(e.target.value) || 0)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Weight (kg) <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              value={formData.weight_kg}
              onChange={(e) => handleInputChange('weight_kg', parseInt(e.target.value) || 0)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
            />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Phone Number <span className="text-red-500">*</span>
          </label>
          <div className={`flex ${formErrors.phone_number ? 'ring-2 ring-red-400 rounded-lg' : ''}`}>
            <span className="inline-flex items-center px-3 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50 text-gray-600">
              +60
            </span>
            <input
              type="tel"
              value={formData.phone_number}
              onChange={(e) => handlePhoneChange(e.target.value)}
              placeholder="01XXXXXXXXX"
              className={`flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors.phone_number ? 'border-red-400 focus:ring-red-400' : ''}`}
            />
          </div>
          {formErrors.phone_number && (
            <p className="mt-1 text-xs text-red-600">{formErrors.phone_number}</p>
          )}
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Email <span className="text-red-500">*</span>
          </label>
          <input
            type="email"
            value={formData.email}
            onChange={(e) => handleInputChange('email', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Password <span className="text-red-500">*</span>
          </label>
          <input
            type="password"
            value={formData.password}
            onChange={(e) => handleInputChange('password', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
          />
        </div>
      </div>
    </div>
  );

  const renderMedicalHistory = (prefix: string = "") => {
    const fieldPrefix = prefix ? `${prefix}_` : "";
    
    return (
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
                name={`${fieldPrefix}medical_consultation_2_years`}
                checked={formData[`${fieldPrefix}medical_consultation_2_years` as keyof RegistrationFormData] as boolean}
                onChange={(e) => handleInputChange(`${fieldPrefix}medical_consultation_2_years` as keyof RegistrationFormData, true)}
                className="mr-2"
              />
              Ya
            </label>
            <label className="flex items-center">
              <input
                type="radio"
                name={`${fieldPrefix}medical_consultation_2_years`}
                checked={!(formData[`${fieldPrefix}medical_consultation_2_years` as keyof RegistrationFormData] as boolean)}
                onChange={(e) => handleInputChange(`${fieldPrefix}medical_consultation_2_years` as keyof RegistrationFormData, false)}
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
                name={`${fieldPrefix}serious_illness_history`}
                checked={formData[`${fieldPrefix}serious_illness_history` as keyof RegistrationFormData] as boolean}
                onChange={(e) => handleInputChange(`${fieldPrefix}serious_illness_history` as keyof RegistrationFormData, true)}
                className="mr-2"
              />
              Ya
            </label>
            <label className="flex items-center">
              <input
                type="radio"
                name={`${fieldPrefix}serious_illness_history`}
                checked={!(formData[`${fieldPrefix}serious_illness_history` as keyof RegistrationFormData] as boolean)}
                onChange={(e) => handleInputChange(`${fieldPrefix}serious_illness_history` as keyof RegistrationFormData, false)}
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
                name={`${fieldPrefix}insurance_rejection_history`}
                checked={formData[`${fieldPrefix}insurance_rejection_history` as keyof RegistrationFormData] as boolean}
                onChange={(e) => handleInputChange(`${fieldPrefix}insurance_rejection_history` as keyof RegistrationFormData, true)}
                className="mr-2"
              />
              Ya
            </label>
            <label className="flex items-center">
              <input
                type="radio"
                name={`${fieldPrefix}insurance_rejection_history`}
                checked={!(formData[`${fieldPrefix}insurance_rejection_history` as keyof RegistrationFormData] as boolean)}
                onChange={(e) => handleInputChange(`${fieldPrefix}insurance_rejection_history` as keyof RegistrationFormData, false)}
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
                name={`${fieldPrefix}serious_injury_history`}
                checked={formData[`${fieldPrefix}serious_injury_history` as keyof RegistrationFormData] as boolean}
                onChange={(e) => handleInputChange(`${fieldPrefix}serious_injury_history` as keyof RegistrationFormData, true)}
                className="mr-2"
              />
              Ya
            </label>
            <label className="flex items-center">
              <input
                type="radio"
                name={`${fieldPrefix}serious_injury_history`}
                checked={!(formData[`${fieldPrefix}serious_injury_history` as keyof RegistrationFormData] as boolean)}
                onChange={(e) => handleInputChange(`${fieldPrefix}serious_injury_history` as keyof RegistrationFormData, false)}
                className="mr-2"
              />
              Tidak
            </label>
          </div>
        </div>
      </div>
    );
  };

  const renderEmergencyContact = (prefix: string = "") => {
    const fieldPrefix = prefix ? `${prefix}_` : "";
    
    return (
      <div className="space-y-4">
        <h4 className="font-medium text-gray-800">Emergency Contact</h4>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Nama Per IC <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={formData[`${fieldPrefix}emergency_contact_name` as keyof RegistrationFormData] as string}
            onChange={(e) => handleInputChange(`${fieldPrefix}emergency_contact_name` as keyof RegistrationFormData, e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Contact No <span className="text-red-500">*</span>
          </label>
          <div className={`flex ${formErrors.emergency_contact_phone ? 'ring-2 ring-red-400 rounded-lg' : ''}`}>
            <span className="inline-flex items-center px-3 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50 text-gray-600">
              +60
            </span>
            <input
              type="tel"
              value={formData[`${fieldPrefix}emergency_contact_phone` as keyof RegistrationFormData] as string}
              onChange={(e) => handleEmergencyPhoneChange(e.target.value)}
              placeholder="01XXXXXXXXX"
              className={`flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${formErrors.emergency_contact_phone ? 'border-red-400 focus:ring-red-400' : ''}`}
            />
          </div>
          {formErrors.emergency_contact_phone && (
            <p className="mt-1 text-xs text-red-600">{formErrors.emergency_contact_phone}</p>
          )}
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Relationship <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={formData[`${fieldPrefix}emergency_contact_relationship` as keyof RegistrationFormData] as string}
            onChange={(e) => handleInputChange(`${fieldPrefix}emergency_contact_relationship` as keyof RegistrationFormData, e.target.value)}
            placeholder="Sila pastikan maklumat ini adalah selain dari penama di atas"
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
          />
        </div>
      </div>
    );
  };

  const renderPaymentSection = (prefix: string = "") => {
    const fieldPrefix = prefix ? `${prefix}_` : "";
    const planName = formData[`${fieldPrefix}plan_type` as keyof RegistrationFormData] as string;
    const paymentMode = formData[`${fieldPrefix}payment_mode` as keyof RegistrationFormData] as string;
    const cardType = formData[`${fieldPrefix}medical_card_type` as keyof RegistrationFormData] as string;
    const totalPrice = getTotalPrice(planName, paymentMode, cardType);
    
    return (
      <div className="space-y-4">
        <h4 className="font-medium text-gray-800">Payment Mode</h4>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Contribution Amount ({planName}): <span className="text-red-500">*</span>
          </label>
          <div className="space-y-2">
            <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
              <span>Monthly (RM{getPlanPrice(planName, 'monthly')})</span>
              <input
                type="radio"
                name={`${fieldPrefix}payment_mode`}
                value="monthly"
                checked={paymentMode === 'monthly'}
                onChange={(e) => handleInputChange(`${fieldPrefix}payment_mode` as keyof RegistrationFormData, e.target.value)}
                className="mr-2"
              />
            </label>
            <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
              <span>Yearly (RM{getPlanPrice(planName, 'yearly')})</span>
              <input
                type="radio"
                name={`${fieldPrefix}payment_mode`}
                value="yearly"
                checked={paymentMode === 'yearly'}
                onChange={(e) => handleInputChange(`${fieldPrefix}payment_mode` as keyof RegistrationFormData, e.target.value)}
                className="mr-2"
              />
            </label>
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Medical Card Type <span className="text-red-500">*</span>
          </label>
          <div className="space-y-2">
            <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
              <span>e-Medical Card</span>
              <input
                type="radio"
                name={`${fieldPrefix}medical_card_type`}
                value="e-Medical Card"
                checked={(formData[`${fieldPrefix}medical_card_type` as keyof RegistrationFormData] as string) === 'e-Medical Card'}
                onChange={(e) => {
                  handleInputChange(`${fieldPrefix}medical_card_type` as keyof RegistrationFormData, e.target.value);
                  // Force re-render to update price
                  setFormData(prev => ({...prev}));
                }}
                className="mr-2"
              />
            </label>
            <label className="flex items-center justify-between p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
              <span>e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)</span>
              <input
                type="radio"
                name={`${fieldPrefix}medical_card_type`}
                value="e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)"
                checked={(formData[`${fieldPrefix}medical_card_type` as keyof RegistrationFormData] as string) === 'e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)'}
                onChange={(e) => {
                  handleInputChange(`${fieldPrefix}medical_card_type` as keyof RegistrationFormData, e.target.value);
                  // Force re-render to update price
                  setFormData(prev => ({...prev}));
                }}
                className="mr-2"
              />
            </label>
          </div>
        </div>

        <div className="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
          <div className="flex justify-between items-center">
            <span className="font-medium text-emerald-800">Total Amount:</span>
            <span className="text-lg font-bold text-emerald-800">RM {totalPrice.toFixed(2)}</span>
          </div>
        </div>
      </div>
    );
  };

  const renderSecondCustomer = () => (
    <div className="space-y-6 border-t pt-6">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-semibold text-gray-800">Add Second Customer</h3>
        <button
          type="button"
          onClick={() => setShowSecondCustomer(!showSecondCustomer)}
          className="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"
        >
          {showSecondCustomer ? <ChevronUp className="w-5 h-5" /> : <ChevronDown className="w-5 h-5" />}
        </button>
      </div>

      <AnimatePresence>
        {showSecondCustomer && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="space-y-6"
          >
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Plan Type (Second Customer) <span className="text-red-500">*</span>
              </label>
              <select
                value={formData.second_customer_plan_type}
                onChange={(e) => handleInputChange('second_customer_plan_type', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
              >
                <option value="">Select Plan</option>
                {plans.map((plan, idx) => (
                  <option key={`${plan.id}-${plan.name}-${idx}`} value={plan.name}>
                    {plan.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Full Name (Second Customer) <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                value={formData.second_customer_full_name}
                onChange={(e) => handleInputChange('second_customer_full_name', e.target.value)}
                placeholder="Cth: Ridhuan Shah"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                NRIC Number (Second Customer) <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                value={formData.second_customer_nric}
                onChange={(e) => handleInputChange('second_customer_nric', e.target.value)}
                placeholder="Cth: 950611-14-7183"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Race (Second Customer) <span className="text-red-500">*</span>
              </label>
              <select
                value={formData.second_customer_race}
                onChange={(e) => handleInputChange('second_customer_race', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
              >
                <option value="Malay">Malay</option>
                <option value="Chinese">Chinese</option>
                <option value="Indian">Indian</option>
                <option value="Others">Others</option>
              </select>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Height (cm) (Second Customer) <span className="text-red-500">*</span>
                </label>
                <input
                  type="number"
                  value={formData.second_customer_height_cm}
                  onChange={(e) => handleInputChange('second_customer_height_cm', parseInt(e.target.value) || 0)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Weight (kg) (Second Customer) <span className="text-red-500">*</span>
                </label>
                <input
                  type="number"
                  value={formData.second_customer_weight_kg}
                  onChange={(e) => handleInputChange('second_customer_weight_kg', parseInt(e.target.value) || 0)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Phone Number (Second Customer) <span className="text-red-500">*</span>
              </label>
              <div className="flex">
                <span className="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-l-lg">
                  60
                </span>
                <input
                  type="tel"
                  value={formData.second_customer_phone_number}
                  onChange={(e) => handleCustomerPhoneChange('second_customer_phone_number', e.target.value)}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>
              {formErrors.second_customer_phone_number && (
                <p className="text-red-500 text-xs mt-1">{formErrors.second_customer_phone_number}</p>
              )}
            </div>

            {renderMedicalHistory('second_customer')}
            {renderPaymentSection('second_customer')}
            
            {/* Emergency Contact for Second Customer */}
            <div className="mt-6 pt-6 border-t border-gray-200">
              <h4 className="font-medium text-gray-800 mb-4">Emergency Contact (Second Customer)</h4>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Contact Name <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formData.second_customer_emergency_contact_name}
                    onChange={(e) => handleInputChange('second_customer_emergency_contact_name', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Contact No <span className="text-red-500">*</span>
                  </label>
                  <div className="flex">
                    <span className="inline-flex items-center px-3 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50 text-gray-600">
                      +60
                    </span>
                    <input
                      type="tel"
                      value={formData.second_customer_emergency_contact_phone}
                      onChange={(e) => handleCustomerPhoneChange('second_customer_emergency_contact_phone', e.target.value)}
                      placeholder="01XXXXXXXXX"
                      className="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Relationship <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formData.second_customer_emergency_contact_relationship}
                    onChange={(e) => handleInputChange('second_customer_emergency_contact_relationship', e.target.value)}
                    placeholder="e.g., Spouse, Parent, Sibling"
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );

  const renderThirdCustomer = () => (
    <div className="space-y-6 border-t pt-6">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-semibold text-gray-800">Add Third Customer</h3>
        <button
          type="button"
          onClick={() => {
            if (!showSecondCustomer) {
              // If second customer is not shown, show it first
              setShowSecondCustomer(true);
            } else {
              setShowThirdCustomer(!showThirdCustomer);
            }
          }}
          className="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"
        >
          {showThirdCustomer ? <ChevronUp className="w-5 h-5" /> : <ChevronDown className="w-5 h-5" />}
        </button>
      </div>

      <AnimatePresence>
        {showSecondCustomer && showThirdCustomer && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="space-y-6"
          >
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Plan Type (Third Customer) <span className="text-red-500">*</span>
              </label>
              <select
                value={formData.third_customer_plan_type}
                onChange={(e) => handleInputChange('third_customer_plan_type', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
              >
                <option value="">Select Plan</option>
                {plans.map((plan, idx) => (
                  <option key={`${plan.id}-${plan.name}-${idx}`} value={plan.name}>
                    {plan.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Full Name (Third Customer) <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                value={formData.third_customer_full_name}
                onChange={(e) => handleInputChange('third_customer_full_name', e.target.value)}
                placeholder="Cth: Ridhuan Shah"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                NRIC Number (Third Customer) <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                value={formData.third_customer_nric}
                onChange={(e) => handleInputChange('third_customer_nric', e.target.value)}
                placeholder="Cth: 950611-14-7183"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Race (Third Customer) <span className="text-red-500">*</span>
              </label>
              <select
                value={formData.third_customer_race}
                onChange={(e) => handleInputChange('third_customer_race', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
              >
                <option value="Malay">Malay</option>
                <option value="Chinese">Chinese</option>
                <option value="Indian">Indian</option>
                <option value="Others">Others</option>
              </select>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Height (cm) (Third Customer) <span className="text-red-500">*</span>
                </label>
                <input
                  type="number"
                  value={formData.third_customer_height_cm}
                  onChange={(e) => handleInputChange('third_customer_height_cm', parseInt(e.target.value) || 0)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Weight (kg) (Third Customer) <span className="text-red-500">*</span>
                </label>
                <input
                  type="number"
                  value={formData.third_customer_weight_kg}
                  onChange={(e) => handleInputChange('third_customer_weight_kg', parseInt(e.target.value) || 0)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Phone Number (Third Customer) <span className="text-red-500">*</span>
              </label>
              <div className="flex">
                <span className="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-l-lg">
                  60
                </span>
                <input
                  type="tel"
                  value={formData.third_customer_phone_number}
                  onChange={(e) => handleInputChange('third_customer_phone_number', e.target.value)}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>
              {formErrors.third_customer_phone_number && (
                <p className="text-red-500 text-xs mt-1">{formErrors.third_customer_phone_number}</p>
              )}
            </div>

            {renderMedicalHistory('third_customer')}
            {renderPaymentSection('third_customer')}
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );

  const renderCustomerForm = (customerNumber: string, isVisible: boolean, setVisible: (visible: boolean) => void) => {
    const customerLabel = {
      'fourth': 'Fourth',
      'fifth': 'Fifth', 
      'sixth': 'Sixth',
      'seventh': 'Seventh',
      'eighth': 'Eighth',
      'ninth': 'Ninth',
      'tenth': 'Tenth'
    }[customerNumber];
    
    // Previous customer number and visibility state
    const prevCustomerNumber = {
      'fourth': 'third',
      'fifth': 'fourth', 
      'sixth': 'fifth',
      'seventh': 'sixth',
      'eighth': 'seventh',
      'ninth': 'eighth',
      'tenth': 'ninth'
    }[customerNumber];
    
    const isPreviousVisible = {
      'fourth': showThirdCustomer,
      'fifth': showFourthCustomer, 
      'sixth': showFifthCustomer,
      'seventh': showSixthCustomer,
      'eighth': showSeventhCustomer,
      'ninth': showEighthCustomer,
      'tenth': showNinthCustomer
    }[customerNumber];

    return (
      <div className="space-y-6 border-t pt-6">
        <div className="flex items-center justify-between">
          <h3 className="text-lg font-semibold text-gray-800">Add {customerLabel} Customer</h3>
          <button
            type="button"
            onClick={() => {
              if (!isPreviousVisible) {
                // If previous customer is not shown, we can't show this one
                alert(`Please add ${prevCustomerNumber === 'third' ? 'Third' : customerLabel} Customer first`);
              } else {
                setVisible(!isVisible);
              }
            }}
            className="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"
          >
            {isVisible ? <ChevronUp className="w-5 h-5" /> : <ChevronDown className="w-5 h-5" />}
          </button>
        </div>

        <AnimatePresence>
          {isPreviousVisible && isVisible && (
            <motion.div
              initial={{ opacity: 0, height: 0 }}
              animate={{ opacity: 1, height: 'auto' }}
              exit={{ opacity: 0, height: 0 }}
              className="space-y-6"
            >
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Plan Type ({customerLabel} Customer) <span className="text-red-500">*</span>
                </label>
                <select
                  value={formData[`${customerNumber}_customer_plan_type` as keyof RegistrationFormData] as string}
                  onChange={(e) => handleInputChange(`${customerNumber}_customer_plan_type` as keyof RegistrationFormData, e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                >
                  <option value="">Select Plan</option>
                  {plans.map((plan, idx) => (
                    <option key={`${plan.id}-${plan.name}-${idx}`} value={plan.name}>
                      {plan.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Full Name ({customerLabel} Customer) <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formData[`${customerNumber}_customer_full_name` as keyof RegistrationFormData] as string}
                  onChange={(e) => handleInputChange(`${customerNumber}_customer_full_name` as keyof RegistrationFormData, e.target.value)}
                  placeholder="Cth: Ridhuan Shah"
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  NRIC Number ({customerLabel} Customer) <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formData[`${customerNumber}_customer_nric` as keyof RegistrationFormData] as string}
                  onChange={(e) => handleInputChange(`${customerNumber}_customer_nric` as keyof RegistrationFormData, e.target.value)}
                  placeholder="Cth: 950611-14-7183"
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Race ({customerLabel} Customer) <span className="text-red-500">*</span>
                </label>
                <select
                  value={formData[`${customerNumber}_customer_race` as keyof RegistrationFormData] as string}
                  onChange={(e) => handleInputChange(`${customerNumber}_customer_race` as keyof RegistrationFormData, e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                >
                  <option value="Malay">Malay</option>
                  <option value="Chinese">Chinese</option>
                  <option value="Indian">Indian</option>
                  <option value="Others">Others</option>
                </select>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Height (cm) ({customerLabel} Customer) <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    value={formData[`${customerNumber}_customer_height_cm` as keyof RegistrationFormData] as number}
                    onChange={(e) => handleInputChange(`${customerNumber}_customer_height_cm` as keyof RegistrationFormData, parseInt(e.target.value) || 0)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Weight (kg) ({customerLabel} Customer) <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    value={formData[`${customerNumber}_customer_weight_kg` as keyof RegistrationFormData] as number}
                    onChange={(e) => handleInputChange(`${customerNumber}_customer_weight_kg` as keyof RegistrationFormData, parseInt(e.target.value) || 0)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Phone Number ({customerLabel} Customer) <span className="text-red-500">*</span>
                </label>
                <div className="flex">
                  <span className="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-l-lg">
                    60
                  </span>
                  <input
                    type="tel"
                    value={formData[`${customerNumber}_customer_phone_number` as keyof RegistrationFormData] as string}
                    onChange={(e) => handleInputChange(`${customerNumber}_customer_phone_number` as keyof RegistrationFormData, e.target.value)}
                    className="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  />
                </div>
                {formErrors[`${customerNumber}_customer_phone_number` as keyof RegistrationFormData] && (
                  <p className="text-red-500 text-xs mt-1">{formErrors[`${customerNumber}_customer_phone_number` as keyof RegistrationFormData]}</p>
                )}
              </div>

              {renderMedicalHistory(`${customerNumber}_customer`)}
              {renderPaymentSection(`${customerNumber}_customer`)}
            </motion.div>
          )}
        </AnimatePresence>
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
              <button
                onClick={onClose}
                className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <X className="w-5 h-5 text-gray-600" />
              </button>
            </div>

            <div className="p-6 space-y-6" key="registration-content">
              {error && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                  <p className="text-red-800 text-sm">{error}</p>
                </div>
              )}

              {renderStep1()}
              {renderMedicalHistory()}
              {renderEmergencyContact()}
              {renderPaymentSection()}

              <div className="flex items-center justify-between">
                <button
                  type="button"
                  onClick={() => setShowSecondCustomer(!showSecondCustomer)}
                  className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                >
                  <Plus className="w-4 h-4" />
                  Add Second Customer
                </button>
              </div>

              {showSecondCustomer && renderSecondCustomer()}

              {showSecondCustomer && (
                <div className="flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => {
                      if (showThirdCustomer) {
                        setShowThirdCustomer(false);
                      } else {
                        setShowThirdCustomer(true);
                        setShowFourthCustomer(false);
                        setShowFifthCustomer(false);
                        setShowSixthCustomer(false);
                        setShowSeventhCustomer(false);
                        setShowEighthCustomer(false);
                        setShowNinthCustomer(false);
                        setShowTenthCustomer(false);
                      }
                    }}
                    className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Third Customer
                  </button>
                </div>
              )}

              {showThirdCustomer && renderThirdCustomer()}

              {showThirdCustomer && (
                <div className="flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => {
                      if (showFourthCustomer) {
                        setShowFourthCustomer(false);
                      } else {
                        setShowFourthCustomer(true);
                        setShowFifthCustomer(false);
                        setShowSixthCustomer(false);
                        setShowSeventhCustomer(false);
                        setShowEighthCustomer(false);
                        setShowNinthCustomer(false);
                        setShowTenthCustomer(false);
                      }
                    }}
                    className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Fourth Customer
                  </button>
                </div>
              )}

              {showFourthCustomer && renderCustomerForm('fourth', showFourthCustomer, setShowFourthCustomer)}

              {showFourthCustomer && (
                <div className="flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => {
                      if (showFifthCustomer) {
                        setShowFifthCustomer(false);
                      } else {
                        setShowFifthCustomer(true);
                        setShowSixthCustomer(false);
                        setShowSeventhCustomer(false);
                        setShowEighthCustomer(false);
                        setShowNinthCustomer(false);
                        setShowTenthCustomer(false);
                      }
                    }}
                    className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Fifth Customer
                  </button>
                </div>
              )}

              {showFifthCustomer && renderCustomerForm('fifth', showFifthCustomer, setShowFifthCustomer)}

              {showFifthCustomer && (
                <div className="flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => {
                      if (showSixthCustomer) {
                        setShowSixthCustomer(false);
                      } else {
                        setShowSixthCustomer(true);
                        setShowSeventhCustomer(false);
                        setShowEighthCustomer(false);
                        setShowNinthCustomer(false);
                        setShowTenthCustomer(false);
                      }
                    }}
                    className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Sixth Customer
                  </button>
                </div>
              )}

              {showSixthCustomer && renderCustomerForm('sixth', showSixthCustomer, setShowSixthCustomer)}

              {showSixthCustomer && (
                <div className="flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => {
                      if (showSeventhCustomer) {
                        setShowSeventhCustomer(false);
                      } else {
                        setShowSeventhCustomer(true);
                        setShowEighthCustomer(false);
                        setShowNinthCustomer(false);
                        setShowTenthCustomer(false);
                      }
                    }}
                    className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Seventh Customer
                  </button>
                </div>
              )}

              {showSeventhCustomer && renderCustomerForm('seventh', showSeventhCustomer, setShowSeventhCustomer)}

              {showSeventhCustomer && (
                <div className="flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => {
                      if (showEighthCustomer) {
                        setShowEighthCustomer(false);
                      } else {
                        setShowEighthCustomer(true);
                        setShowNinthCustomer(false);
                        setShowTenthCustomer(false);
                      }
                    }}
                    className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Eighth Customer
                  </button>
                </div>
              )}

              {showEighthCustomer && renderCustomerForm('eighth', showEighthCustomer, setShowEighthCustomer)}

              {showEighthCustomer && (
                <div className="flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => {
                      if (showNinthCustomer) {
                        setShowNinthCustomer(false);
                      } else {
                        setShowNinthCustomer(true);
                        setShowTenthCustomer(false);
                      }
                    }}
                    className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Ninth Customer
                  </button>
                </div>
              )}

              {showNinthCustomer && renderCustomerForm('ninth', showNinthCustomer, setShowNinthCustomer)}

              {showNinthCustomer && (
                <div className="flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => setShowTenthCustomer(!showTenthCustomer)}
                    className="flex items-center gap-2 px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Tenth Customer
                  </button>
                </div>
              )}

              {showTenthCustomer && renderCustomerForm('tenth', showTenthCustomer, setShowTenthCustomer)}
            </div>

            <div className="p-6 border-t border-gray-200">
              <div className="flex gap-3 justify-end">
                <button
                  onClick={onClose}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
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
        />
      )}
    </AnimatePresence>
  );
}

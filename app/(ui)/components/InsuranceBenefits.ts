// Insurance plan benefits data
export const getInsuranceBenefits = (planName: string) => {
  const plan = planName || 'MediPlan Coop';
  
  switch (plan) {
    case 'MediPlan Coop':
      return [
        { title: "Room & Board", desc: "Daily allowance", amt: "250.00" },
        { title: "Ambulance Fees", desc: "Included", amt: "Included" },
        { title: "Intensive Care Unit", desc: "Full coverage", amt: "As Charged" },
        { title: "Hospital Supplies & Services", desc: "Full coverage", amt: "As Charged" },
        { title: "Surgical Fees", desc: "Full coverage", amt: "As Charged" },
        { title: "Operating Theater Fees", desc: "Full coverage", amt: "As Charged" },
        { title: "Anesthetist Fees", desc: "Full coverage", amt: "As Charged" },
        { title: "In-hospital Doctor Visit", desc: "Full coverage", amt: "As Charged" },
        { title: "Day Care & Day Surgery", desc: "Full coverage", amt: "As Charged" },
        { title: "Second Surgical / Treatment Opinion", desc: "Full coverage", amt: "As Charged" },
        { title: "Emergency Accidental Dental Treatment", desc: "Full coverage", amt: "As Charged" },
        { title: "Covid Test for Admission Purpose", desc: "Full coverage", amt: "As Charged" },
        { title: "Daily Cash Allowance in Government Hospital", desc: "Up to maximum of 120 days per year", amt: "100.00" },
        { title: "Pre-Hospital Diagnostic Test & Consultation", desc: "Per admission, applicable 30 days prior to admission to hospital", amt: "5,000.00" },
        { title: "Accidental Injury Surgery / Treatment", desc: "Applicable for in-patient treatment and must first be paid by the Member before their case can be submitted for claim during the Waiting Period Post Waiting Period, the standard in-patient medical cost of RM1,000,000 applies", amt: "10,000.00" },
        { title: "Bereavement", desc: "Upon death, RM10,000 will be paid to their appointed beneficiary as listed in the system", amt: "10,000.00" },
        { title: "Out-patient Cancer Treatment", desc: "Chemotherapy, Radiotherapy and Electrotherapy are eligible for sharing up to RM100,000, which is excluded from the RM1,000,000 In-Patient Hospitalization Medical Expenses Required to complete the 180-day Waiting Period", amt: "100,000.00" },
        { title: "Conditional Outpatient Benefits", desc: "Specified clinical outpatient benefits for common medical conditions list", amt: "As Charged" },
        { title: "ANNUAL LIMIT", desc: "Total annual coverage", amt: "1,000,000.00" }
      ];
    case 'Senior Care Plan Gold 270':
      return [
        { title: "Hospital Room & Board", desc: "Daily max up to 180 days", amt: "270.00" },
        { title: "Intensive Care Unit", desc: "Daily max up 30 days", amt: "Full Reimbursement" },
        { title: "Hospital Supplies and Services", desc: "Full coverage", amt: "As Charged" },
        { title: "Surgeon Fee", desc: "Full coverage", amt: "As Charged" },
        { title: "Anaesthetist Fee", desc: "Full coverage", amt: "As Charged" },
        { title: "Operating Theatre Charges", desc: "Full coverage", amt: "As Charged" },
        { title: "Daily in-Hospital Physician's Visit", desc: "Max. 180 days", amt: "As Charged" },
        { title: "Pre-Hospital Diagnostic Tests", desc: "Within 60 days before hospital confinement", amt: "As Charged" },
        { title: "Pre-Hospitalization Specialist Consultation", desc: "Within 60 days before hospital confinement", amt: "As Charged" },
        { title: "Second Surgical Opinion", desc: "Within 60 days before hospital confinement", amt: "As Charged" },
        { title: "Post-Hospitalization Treatment", desc: "Within 60 days from hospital discharge", amt: "As Charged" },
        { title: "Emergency Accidental Outpatient Treatment", desc: "Within 24 hours after the accident & follow-up up to 60 days", amt: "As Charged" },
        { title: "Outpatient Cancer Treatment", desc: "Full coverage", amt: "As Charged" },
        { title: "Outpatient Kidney Dialysis Treatment", desc: "Full coverage", amt: "As Charged" },
        { title: "Daycare Procedure", desc: "Full coverage", amt: "As Charged" },
        { title: "Ambulance Charges", desc: "by road", amt: "As Charged" },
        { title: "Government Service Tax", desc: "Full coverage", amt: "As Charged" },
        { title: "Government Hospital Daily Cash Allowance", desc: "Max. 180 days", amt: "100.00" },
        { title: "Medical Report Fee Reimbursement", desc: "Full coverage", amt: "80.00" },
        { title: "Funeral Expenses - Accidental (Hospitalized)", desc: "Full coverage", amt: "10,000.00" },
        { title: "OVERALL ANNUAL LIMIT", desc: "Total annual coverage", amt: "75,000.00" }
      ];
    case 'Senior Care Plan Diamond 370':
      return [
        { title: "Hospital Room & Board", desc: "Daily max up to 180 days", amt: "370.00" },
        { title: "Intensive Care Unit", desc: "Daily max up 30 days", amt: "Full Reimbursement" },
        { title: "Hospital Supplies and Services", desc: "Full coverage", amt: "As Charged" },
        { title: "Surgeon Fee", desc: "Full coverage", amt: "As Charged" },
        { title: "Anaesthetist Fee", desc: "Full coverage", amt: "As Charged" },
        { title: "Operating Theatre Charges", desc: "Full coverage", amt: "As Charged" },
        { title: "Daily in-Hospital Physician's Visit", desc: "Max. 180 days", amt: "As Charged" },
        { title: "Pre-Hospital Diagnostic Tests", desc: "Full coverage", amt: "As Charged" },
        { title: "Pre-Hospitalization Specialist Consultation", desc: "Full coverage", amt: "As Charged" },
        { title: "Second Surgical Opinion", desc: "Full coverage", amt: "As Charged" },
        { title: "Post-Hospitalization Treatment", desc: "Full coverage", amt: "As Charged" },
        { title: "Emergency Accidental Outpatient Treatment", desc: "Full coverage", amt: "As Charged" },
        { title: "Outpatient Cancer Treatment", desc: "Full coverage", amt: "As Charged" },
        { title: "Outpatient Kidney Dialysis Treatment", desc: "Full coverage", amt: "As Charged" },
        { title: "Daycare Procedure", desc: "Full coverage", amt: "As Charged" },
        { title: "Ambulance Charges", desc: "by road", amt: "As Charged" },
        { title: "Government Service Tax", desc: "Full coverage", amt: "As Charged" },
        { title: "Government Hospital Daily Cash Allowance", desc: "Max. 180 days", amt: "200.00" },
        { title: "Medical Report Fee Reimbursement", desc: "Full coverage", amt: "80.00" },
        { title: "Funeral Expenses - Accidental (Hospitalized)", desc: "Full coverage", amt: "10,000.00" },
        { title: "OVERALL ANNUAL LIMIT", desc: "Total annual coverage", amt: "100,000.00" }
      ];
    default:
      return [];
  }
};

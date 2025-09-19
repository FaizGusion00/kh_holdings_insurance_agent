const axios = require('axios');

// Test Complete Flow
async function testCompleteFlow() {
  try {
    console.log('Testing Complete End-to-End Flow...');
    const timestamp = Date.now();
    
    // 1. Register an upline agent first
    console.log('\n1. Registering an upline agent...');
    const uplineRegResponse = await axios.post('http://localhost:8000/api/v1/auth/register', {
      name: `Upline Agent ${timestamp}`,
      email: `upline.agent.${timestamp}@example.com`,
      phone_number: `01${Math.floor(10000000 + Math.random() * 90000000)}`,
      nric: `${900101}${Math.floor(10 + Math.random() * 90)}${Math.floor(1000 + Math.random() * 9000)}`,
      race: 'Malay',
      date_of_birth: '1990-01-01',
      gender: 'Male',
      occupation: 'Software Engineer',
      emergency_contact_name: 'Emergency Contact',
      emergency_contact_phone: '0123456789',
      emergency_contact_relationship: 'Spouse',
      address: '123 Main Street',
      city: 'Kuala Lumpur',
      state: 'Wilayah Persekutuan',
      postal_code: '50000',
      password: 'password123',
      password_confirmation: 'password123'
    });
    
    if (uplineRegResponse.data.status !== 'success') {
      throw new Error(`Upline agent registration failed: ${JSON.stringify(uplineRegResponse.data)}`);
    }
    
    console.log('Upline agent registered successfully:', uplineRegResponse.data.data.user.name);
    const uplineToken = uplineRegResponse.data.data.access_token;
    
    // Set agent code for the upline agent
    console.log('Setting agent code for the upline agent...');
    const uplineRandomCode = Math.floor(10000 + Math.random() * 90000);
    const uplineCodeResponse = await axios.post('http://localhost:8000/api/v1/auth/update-agent-code', {
      user_id: uplineRegResponse.data.data.user.id,
      agent_code: `AGT${uplineRandomCode}`
    }, {
      headers: { Authorization: `Bearer ${uplineToken}` }
    });
    
    if (uplineCodeResponse.data.status !== 'success') {
      throw new Error(`Failed to set upline agent code: ${JSON.stringify(uplineCodeResponse.data)}`);
    }
    
    const uplineAgentCode = uplineCodeResponse.data.data.agent_code;
    console.log('Upline agent code set to:', uplineAgentCode);
    
    // 2. Register a downline agent
    console.log('\n2. Registering a downline agent...');
    const agentRegResponse = await axios.post('http://localhost:8000/api/v1/auth/register', {
      name: `Test Agent ${timestamp}`,
      email: `test.agent.${timestamp}@example.com`,
      phone_number: `01${Math.floor(10000000 + Math.random() * 90000000)}`,
      nric: `${900101}${Math.floor(10 + Math.random() * 90)}${Math.floor(1000 + Math.random() * 9000)}`,
      race: 'Malay',
      date_of_birth: '1990-01-01',
      gender: 'Male',
      occupation: 'Software Engineer',
      emergency_contact_name: 'Emergency Contact',
      emergency_contact_phone: '0123456789',
      emergency_contact_relationship: 'Spouse',
      address: '123 Main Street',
      city: 'Kuala Lumpur',
      state: 'Wilayah Persekutuan',
      postal_code: '50000',
      password: 'password123',
      password_confirmation: 'password123',
      referrer_code: uplineAgentCode
    });
    
    if (agentRegResponse.data.status !== 'success') {
      throw new Error(`Agent registration failed: ${JSON.stringify(agentRegResponse.data)}`);
    }
    
    console.log('Agent registered successfully:', agentRegResponse.data.data.user.name);
    const token = agentRegResponse.data.data.access_token;
    
    // Set agent code for the agent
    console.log('Setting agent code for the agent...');
    const randomCode = Math.floor(10000 + Math.random() * 90000);
    const agentCodeResponse = await axios.post('http://localhost:8000/api/v1/auth/update-agent-code', {
      user_id: agentRegResponse.data.data.user.id,
      agent_code: `AGT${randomCode}`
    }, {
      headers: { Authorization: `Bearer ${token}` }
    });
    
    if (agentCodeResponse.data.status !== 'success') {
      throw new Error(`Failed to set agent code: ${JSON.stringify(agentCodeResponse.data)}`);
    }
    
    const agentCode = agentCodeResponse.data.data.agent_code;
    console.log('Agent code set to:', agentCode);
    
    // 3. Register multiple clients
    console.log('\n3. Registering multiple clients...');
    const clients = [];
    for (let i = 1; i <= 3; i++) {
      const clientNric = `${910101}${Math.floor(10 + Math.random() * 90)}${Math.floor(1000 + Math.random() * 9000)}`;
      const clientEmail = `test.client.${i}.${timestamp}@example.com`;
      
      clients.push({
        plan_type: 'medical',
        full_name: `Test Client ${i} ${timestamp}`,
        nric: clientNric,
        race: 'Chinese',
        height_cm: 170,
        weight_kg: 65,
        phone_number: `01${Math.floor(10000000 + Math.random() * 90000000)}`,
        email: clientEmail,
        medical_consultation_2_years: false,
        serious_illness_history: false,
        insurance_rejection_history: false,
        serious_injury_history: false,
        emergency_contact_name: 'Emergency Contact',
        emergency_contact_phone: '0123456789',
        emergency_contact_relationship: 'Parent',
        payment_mode: 'monthly',
        medical_card_type: 'standard'
      });
    }
    
    const registrationResponse = await axios.post(
      'http://localhost:8000/api/v1/medical-registration/register',
      { clients },
      { headers: { Authorization: `Bearer ${token}` } }
    );
    
    if (registrationResponse.data.status !== 'success') {
      throw new Error(`Client registration failed: ${JSON.stringify(registrationResponse.data)}`);
    }
    
    console.log(`${clients.length} clients registered successfully`);
    const registrationId = registrationResponse.data.data.registration_id;
    const policyIds = registrationResponse.data.data.policies.map(p => p.id);
    const totalAmount = registrationResponse.data.data.total_amount;
    
    console.log('Registration ID:', registrationId);
    console.log('Policy IDs:', policyIds);
    console.log('Total Amount:', totalAmount);
    
    // 4. Create payment
    console.log('\n4. Creating payment...');
    const paymentResponse = await axios.post(
      'http://localhost:8000/api/v1/medical-registration/payment',
      {
        registration_id: registrationId,
        policy_ids: policyIds,
        payment_method: 'curlec',
        return_url: 'http://localhost:3000/payment/success',
        cancel_url: 'http://localhost:3000/payment/cancel'
      },
      { headers: { Authorization: `Bearer ${token}` } }
    );
    
    if (paymentResponse.data.status !== 'success') {
      throw new Error(`Payment creation failed: ${JSON.stringify(paymentResponse.data)}`);
    }
    
    console.log('Payment created successfully:', paymentResponse.data.data.payment.id);
    const paymentId = paymentResponse.data.data.payment.id;
    
    // 5. Simulate payment verification
    console.log('\n5. Simulating payment verification...');
    const verifyResponse = await axios.post(
      'http://localhost:8000/api/v1/medical-registration/verify',
      {
        payment_id: paymentId,
        status: 'success',
        external_ref: `test_payment_${timestamp}`,
        order_id: `test_order_${timestamp}`
      },
      { headers: { Authorization: `Bearer ${token}` } }
    );
    
    if (verifyResponse.data.status !== 'success') {
      throw new Error(`Payment verification failed: ${JSON.stringify(verifyResponse.data)}`);
    }
    
    console.log('Payment verified successfully');
    
    // 6. Get receipt
    console.log('\n6. Getting receipt...');
    const receiptResponse = await axios.get(
      `http://localhost:8000/api/v1/medical-registration/receipt/${paymentId}`,
      { headers: { Authorization: `Bearer ${token}` } }
    );
    
    if (receiptResponse.data.status !== 'success') {
      throw new Error(`Receipt retrieval failed: ${JSON.stringify(receiptResponse.data)}`);
    }
    
    console.log('Receipt retrieved successfully');
    console.log('Receipt details:', receiptResponse.data.data.receipt);
    
    // 7. Check agent wallet
    console.log('\n7. Checking agent wallet...');
    const walletResponse = await axios.get(
      'http://localhost:8000/api/v1/wallet',
      { headers: { Authorization: `Bearer ${token}` } }
    );
    
    if (walletResponse.data.status !== 'success') {
      throw new Error(`Wallet retrieval failed: ${JSON.stringify(walletResponse.data)}`);
    }
    
    console.log('Wallet balance:', walletResponse.data.data.balance);
    console.log('Total earned:', walletResponse.data.data.total_earned);
    
    // 8. Check upline agent wallet
    console.log('\n8. Checking upline agent wallet...');
    const uplineWalletResponse = await axios.get(
      'http://localhost:8000/api/v1/wallet',
      { headers: { Authorization: `Bearer ${uplineToken}` } }
    );
    
    if (uplineWalletResponse.data.status !== 'success') {
      throw new Error(`Upline wallet retrieval failed: ${JSON.stringify(uplineWalletResponse.data)}`);
    }
    
    console.log('Upline wallet balance:', uplineWalletResponse.data.data.balance);
    console.log('Upline total earned:', uplineWalletResponse.data.data.total_earned);
    
    // 9. Create withdrawal request for upline agent
    console.log('\n9. Creating withdrawal request for upline agent...');
    const withdrawalResponse = await axios.post(
      'http://localhost:8000/api/v1/wallet/withdraw',
      {
        amount: 5, // Withdraw 5 RM
        bank_name: 'Test Bank',
        bank_account_number: '1234567890',
        bank_account_owner: `Upline Agent ${timestamp}`
      },
      { headers: { Authorization: `Bearer ${uplineToken}` } }
    );
    
    if (withdrawalResponse.data.status !== 'success') {
      throw new Error(`Withdrawal request failed: ${JSON.stringify(withdrawalResponse.data)}`);
    }
    
    console.log('Withdrawal request created successfully');
    console.log('Withdrawal request details:', withdrawalResponse.data.data);
    
    // 10. Check withdrawal requests for upline agent
    console.log('\n10. Checking withdrawal requests for upline agent...');
    const withdrawalsResponse = await axios.get(
      'http://localhost:8000/api/v1/wallet/withdrawals',
      { headers: { Authorization: `Bearer ${uplineToken}` } }
    );
    
    if (withdrawalsResponse.data.status !== 'success') {
      throw new Error(`Withdrawals retrieval failed: ${JSON.stringify(withdrawalsResponse.data)}`);
    }
    
    console.log('Withdrawal requests retrieved successfully');
    console.log('Withdrawal requests:', withdrawalsResponse.data.data);
    
    console.log('\nTest completed successfully!');
  } catch (error) {
    console.error('Test failed:', error.message);
    if (error.response) {
      console.error('Response data:', error.response.data);
      console.error('Response status:', error.response.status);
    }
  }
}

testCompleteFlow();

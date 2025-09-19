const axios = require('axios');

// Test MLM Commission Flow
async function testMlmCommissionFlow() {
  try {
    console.log('Testing MLM Commission Flow...');
    const timestamp = Date.now();
    const agents = [];
    const tokens = [];
    
    // 1. Register 5 agents in a chain (L1 to L5)
    console.log('1. Registering 5 agents in a chain...');
    
    // Register L1 agent (top of chain)
    const l1Response = await axios.post('http://localhost:8000/api/v1/auth/register', {
      name: `L1 Agent ${timestamp}`,
      email: `l1.agent.${timestamp}@example.com`,
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
    
    if (l1Response.data.status !== 'success') {
      throw new Error(`L1 agent registration failed: ${JSON.stringify(l1Response.data)}`);
    }
    
    const l1Agent = l1Response.data.data.user;
    const l1Token = l1Response.data.data.access_token;
    agents.push(l1Agent);
    tokens.push(l1Token);
    
    console.log(`L1 Agent registered: ${l1Agent.name} with code ${l1Agent.agent_code || 'pending'}`);
    
    // Make a payment for L1 to get an agent code
    if (!l1Agent.agent_code) {
      console.log('Making payment for L1 agent to get agent code...');
      const l1Plan = await axios.get('http://localhost:8000/api/v1/plans', {
        headers: { Authorization: `Bearer ${l1Token}` }
      });
      
      console.log('Available plans:', l1Plan.data.data.plans.map(p => p.plan_code));
      const medicalPlan = l1Plan.data.data.plans.find(p => p.plan_code === 'medical');
      if (!medicalPlan) {
        throw new Error('Medical plan not found in available plans');
      }
      
      const l1PolicyResponse = await axios.post('http://localhost:8000/api/v1/policies/purchase', {
        insurance_plan_id: medicalPlan.id,
        payment_mode: 'monthly'
      }, {
        headers: { Authorization: `Bearer ${l1Token}` }
      });
      
      if (l1PolicyResponse.data.status !== 'success') {
        throw new Error(`L1 policy purchase failed: ${JSON.stringify(l1PolicyResponse.data)}`);
      }
      
      const l1Policy = l1PolicyResponse.data.data.policy;
      
      const l1PaymentResponse = await axios.post('http://localhost:8000/api/v1/payments/create', {
        member_policy_id: l1Policy.id,
        payment_method: 'curlec',
        return_url: 'http://localhost:3000/payment/success',
        cancel_url: 'http://localhost:3000/payment/cancel'
      }, {
        headers: { Authorization: `Bearer ${l1Token}` }
      });
      
      if (l1PaymentResponse.data.status !== 'success') {
        throw new Error(`L1 payment failed: ${JSON.stringify(l1PaymentResponse.data)}`);
      }
      
      const l1Payment = l1PaymentResponse.data.data.payment;
      
      const l1VerifyResponse = await axios.post('http://localhost:8000/api/v1/payments/verify', {
        order_id: l1Payment.external_ref,
        razorpay_payment_id: `test_payment_${timestamp}_l1`,
        razorpay_signature: 'valid_signature',
        status: 'paid'
      }, {
        headers: { Authorization: `Bearer ${l1Token}` }
      });
      
      if (l1VerifyResponse.data.status !== 'success') {
        throw new Error(`L1 payment verification failed: ${JSON.stringify(l1VerifyResponse.data)}`);
      }
      
      // Get updated agent info with agent_code
      const l1UpdatedResponse = await axios.get('http://localhost:8000/api/v1/auth/me', {
        headers: { Authorization: `Bearer ${l1Token}` }
      });
      
      agents[0] = l1UpdatedResponse.data.data.user;
      console.log(`L1 Agent updated with code: ${agents[0].agent_code}`);
    }
    
    // First, make sure L1 agent has an agent code
    if (!agents[0].agent_code) {
      console.log('Setting agent code for L1 agent...');
      const l1UpdateResponse = await axios.post('http://localhost:8000/api/v1/auth/update-agent-code', {
        user_id: agents[0].id,
        agent_code: `AGT${timestamp.toString().substring(0, 5)}`
      }, {
        headers: { Authorization: `Bearer ${tokens[0]}` }
      });
      
      if (l1UpdateResponse.data.status === 'success') {
        agents[0].agent_code = l1UpdateResponse.data.data.agent_code;
        console.log(`L1 Agent code set to: ${agents[0].agent_code}`);
      } else {
        console.log('Failed to set L1 agent code:', l1UpdateResponse.data);
      }
    }
    
    // Register L2-L5 agents, each referring to the previous one
    for (let level = 2; level <= 5; level++) {
      const prevAgent = agents[level - 2];
      
      // Make sure previous agent has an agent code
      if (!prevAgent.agent_code) {
        console.log(`Setting agent code for L${level-1} agent...`);
        const updateResponse = await axios.post('http://localhost:8000/api/v1/auth/update-agent-code', {
          user_id: prevAgent.id,
          agent_code: `AGT${timestamp.toString().substring(0, 3)}${level-1}`
        }, {
          headers: { Authorization: `Bearer ${tokens[level-2]}` }
        });
        
        if (updateResponse.data.status === 'success') {
          prevAgent.agent_code = updateResponse.data.data.agent_code;
          console.log(`L${level-1} Agent code set to: ${prevAgent.agent_code}`);
        } else {
          console.log(`Failed to set L${level-1} agent code:`, updateResponse.data);
        }
      }
      
      const response = await axios.post('http://localhost:8000/api/v1/auth/register', {
        name: `L${level} Agent ${timestamp}`,
        email: `l${level}.agent.${timestamp}@example.com`,
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
        referrer_code: prevAgent.agent_code
      });
      
      if (response.data.status !== 'success') {
        throw new Error(`L${level} agent registration failed: ${JSON.stringify(response.data)}`);
      }
      
      const agent = response.data.data.user;
      const token = response.data.data.access_token;
      agents.push(agent);
      tokens.push(token);
      
      console.log(`L${level} Agent registered: ${agent.name} with referrer ${prevAgent.agent_code}`);
      
      // Make a payment for this agent to get an agent code
      console.log(`Making payment for L${level} agent to get agent code...`);
      const planResponse = await axios.get('http://localhost:8000/api/v1/plans', {
        headers: { Authorization: `Bearer ${token}` }
      });
      
      console.log('Available plans for L' + level + ':', planResponse.data.data.plans.map(p => p.plan_code));
      const medicalPlan = planResponse.data.data.plans.find(p => p.plan_code === 'medical');
      if (!medicalPlan) {
        throw new Error('Medical plan not found in available plans for L' + level);
      }
      
      const policyResponse = await axios.post('http://localhost:8000/api/v1/policies/purchase', {
        insurance_plan_id: medicalPlan.id,
        payment_mode: 'monthly'
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });
      
      if (policyResponse.data.status !== 'success') {
        throw new Error(`L${level} policy purchase failed: ${JSON.stringify(policyResponse.data)}`);
      }
      
      const policy = policyResponse.data.data.policy;
      
      const paymentResponse = await axios.post('http://localhost:8000/api/v1/payments/create', {
        member_policy_id: policy.id,
        payment_method: 'curlec',
        return_url: 'http://localhost:3000/payment/success',
        cancel_url: 'http://localhost:3000/payment/cancel'
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });
      
      if (paymentResponse.data.status !== 'success') {
        throw new Error(`L${level} payment failed: ${JSON.stringify(paymentResponse.data)}`);
      }
      
      const payment = paymentResponse.data.data.payment;
      
      const verifyResponse = await axios.post('http://localhost:8000/api/v1/payments/verify', {
        order_id: payment.external_ref,
        razorpay_payment_id: `test_payment_${timestamp}_l${level}`,
        razorpay_signature: 'valid_signature',
        status: 'paid'
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });
      
      if (verifyResponse.data.status !== 'success') {
        throw new Error(`L${level} payment verification failed: ${JSON.stringify(verifyResponse.data)}`);
      }
      
      // Get updated agent info with agent_code
      const updatedResponse = await axios.get('http://localhost:8000/api/v1/auth/me', {
        headers: { Authorization: `Bearer ${token}` }
      });
      
      agents[level - 1] = updatedResponse.data.data.user;
      console.log(`L${level} Agent updated with code: ${agents[level - 1].agent_code}`);
    }
    
    // 2. Register a client under L5 agent and make a payment
    console.log('\n2. Registering a client under L5 agent...');
    const l5Agent = agents[4];
    const l5Token = tokens[4];
    
    const clientNric = `${910101}${Math.floor(10 + Math.random() * 90)}${Math.floor(1000 + Math.random() * 9000)}`;
    const clientEmail = `test.client.${timestamp}@example.com`;
    
    const registrationResponse = await axios.post(
      'http://localhost:8000/api/v1/medical-registration/register',
      {
        clients: [{
          plan_type: 'medical', // Using plan_code
          full_name: `Test Client ${timestamp}`,
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
        }]
      },
      {
        headers: { Authorization: `Bearer ${l5Token}` }
      }
    );
    
    if (registrationResponse.data.status !== 'success') {
      throw new Error(`Client registration failed: ${JSON.stringify(registrationResponse.data)}`);
    }
    
    console.log('Client registered successfully:', registrationResponse.data.data.clients[0].name);
    const registrationId = registrationResponse.data.data.registration_id;
    const policyIds = registrationResponse.data.data.policies.map(p => p.id);
    
    // Create payment
    console.log('3. Creating payment...');
    const paymentResponse = await axios.post(
      'http://localhost:8000/api/v1/medical-registration/payment',
      {
        registration_id: registrationId,
        policy_ids: policyIds,
        payment_method: 'curlec',
        return_url: 'http://localhost:3000/payment/success',
        cancel_url: 'http://localhost:3000/payment/cancel'
      },
      {
        headers: { Authorization: `Bearer ${l5Token}` }
      }
    );
    
    if (paymentResponse.data.status !== 'success') {
      throw new Error(`Payment creation failed: ${JSON.stringify(paymentResponse.data)}`);
    }
    
    console.log('Payment created successfully:', paymentResponse.data.data.payment.id);
    const paymentId = paymentResponse.data.data.payment.id;
    
    // Simulate payment verification
    console.log('4. Simulating payment verification...');
    const verifyResponse = await axios.post(
      'http://localhost:8000/api/v1/medical-registration/verify',
      {
        payment_id: paymentId,
        status: 'success',
        external_ref: `test_payment_${timestamp}`,
        order_id: `test_order_${timestamp}`
      },
      {
        headers: { Authorization: `Bearer ${l5Token}` }
      }
    );
    
    if (verifyResponse.data.status !== 'success') {
      throw new Error(`Payment verification failed: ${JSON.stringify(verifyResponse.data)}`);
    }
    
    console.log('Payment verified successfully');
    
    // Check all agent wallets for commissions
    console.log('\n5. Checking all agent wallets for commissions...');
    
    for (let i = 0; i < agents.length; i++) {
      const level = i + 1;
      const agent = agents[i];
      const token = tokens[i];
      
      const walletResponse = await axios.get(
        'http://localhost:8000/api/v1/wallet',
        {
          headers: { Authorization: `Bearer ${token}` }
        }
      );
      
      if (walletResponse.data.status !== 'success') {
        console.log(`L${level} wallet retrieval failed: ${JSON.stringify(walletResponse.data)}`);
        continue;
      }
      
      console.log(`L${level} Agent ${agent.name} wallet:`);
      console.log(`  Balance: ${walletResponse.data.data.balance}`);
      console.log(`  Total earned: ${walletResponse.data.data.total_earned}`);
      
      // Check commission transactions
      const commissionResponse = await axios.get(
        'http://localhost:8000/api/v1/mlm/commission-history',
        {
          headers: { Authorization: `Bearer ${token}` }
        }
      );
      
      if (commissionResponse.data.status === 'success' && commissionResponse.data.data.transactions) {
        console.log(`  Commission transactions: ${commissionResponse.data.data.transactions.length}`);
        commissionResponse.data.data.transactions.forEach(tx => {
          console.log(`    - ${tx.amount} (Level ${tx.level}) from ${tx.source_name}`);
        });
      } else {
        console.log('  No commission transactions found');
      }
    }
    
    console.log('\nTest completed successfully!');
  } catch (error) {
    console.error('Test failed:', error.message);
    if (error.response) {
      console.error('Response data:', error.response.data);
      console.error('Response status:', error.response.status);
    }
  }
}

testMlmCommissionFlow();

<template>
  <div class="login-page-container">
    <div class="login-wrapper">
      <!-- Login Form Container -->
      <div class="login-card">
        <LoginForm />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import LoginForm from '../components/LoginForm.vue'
</script>

<style scoped>
.login-page-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-middle) 50%, var(--gradient-end) 100%);
  padding: 1rem;
  box-sizing: border-box;
}

.login-wrapper {
  width: 100%;
  max-width: 28rem;
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 1.5rem;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 10px 25px -5px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 2rem;
  animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 640px) {
  .login-page-container {
    padding: 0.75rem;
  }
  
  .login-card {
    padding: 1.5rem;
  }
}
</style>

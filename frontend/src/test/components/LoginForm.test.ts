import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import LoginForm from '@/components/LoginForm.vue'

// Mock the auth service
vi.mock('@/services/auth', () => ({
  login: vi.fn(),
}))

describe('LoginForm', () => {
  let wrapper: any
  let pinia: any

  beforeEach(() => {
    pinia = createPinia()
    setActivePinia(pinia)
    
    wrapper = mount(LoginForm, {
      global: {
        plugins: [pinia],
      },
    })
  })

  it('renders login form correctly', () => {
    expect(wrapper.find('form').exists()).toBe(true)
    expect(wrapper.find('input[type="email"]').exists()).toBe(true)
    expect(wrapper.find('input[type="password"]').exists()).toBe(true)
    expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
  })

  it('has correct form fields', () => {
    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')
    const submitButton = wrapper.find('button[type="submit"]')

    expect(emailInput.attributes('placeholder')).toContain('email')
    expect(passwordInput.attributes('placeholder')).toContain('password')
    expect(submitButton.text()).toContain('Iniciar')
  })

  it('updates form data when user types', async () => {
    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')

    await emailInput.setValue('test@example.com')
    await passwordInput.setValue('password123')

    expect(wrapper.vm.form.email).toBe('test@example.com')
    expect(wrapper.vm.form.password).toBe('password123')
  })

  it('shows validation errors for empty fields', async () => {
    const submitButton = wrapper.find('button[type="submit"]')
    await submitButton.trigger('click')

    expect(wrapper.vm.errors.email).toBeTruthy()
    expect(wrapper.vm.errors.password).toBeTruthy()
  })

  it('shows validation error for invalid email', async () => {
    const emailInput = wrapper.find('input[type="email"]')
    await emailInput.setValue('invalid-email')
    
    const submitButton = wrapper.find('button[type="submit"]')
    await submitButton.trigger('click')

    expect(wrapper.vm.errors.email).toBeTruthy()
  })

  it('calls login function when form is submitted with valid data', async () => {
    const { login } = await import('@/services/auth')
    
    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')
    const form = wrapper.find('form')

    await emailInput.setValue('test@example.com')
    await passwordInput.setValue('password123')
    await form.trigger('submit')

    expect(login).toHaveBeenCalledWith({
      email: 'test@example.com',
      password: 'password123',
    })
  })

  it('shows loading state during login', async () => {
    const { login } = await import('@/services/auth')
    vi.mocked(login).mockImplementation(() => new Promise(resolve => setTimeout(resolve, 100)))

    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')
    const form = wrapper.find('form')

    await emailInput.setValue('test@example.com')
    await passwordInput.setValue('password123')
    await form.trigger('submit')

    expect(wrapper.vm.loading).toBe(true)
  })

  it('shows error message when login fails', async () => {
    const { login } = await import('@/services/auth')
    vi.mocked(login).mockRejectedValue(new Error('Login failed'))

    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')
    const form = wrapper.find('form')

    await emailInput.setValue('test@example.com')
    await passwordInput.setValue('password123')
    await form.trigger('submit')

    await wrapper.vm.$nextTick()
    expect(wrapper.vm.errorMessage).toBe('Login failed')
  })

  it('clears error message when user starts typing', async () => {
    wrapper.vm.errorMessage = 'Some error'
    
    const emailInput = wrapper.find('input[type="email"]')
    await emailInput.trigger('input')

    expect(wrapper.vm.errorMessage).toBe('')
  })

  it('disables submit button when loading', async () => {
    wrapper.vm.loading = true
    await wrapper.vm.$nextTick()

    const submitButton = wrapper.find('button[type="submit"]')
    expect(submitButton.attributes('disabled')).toBeDefined()
  })

  it('has proper form accessibility attributes', () => {
    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')
    const submitButton = wrapper.find('button[type="submit"]')

    expect(emailInput.attributes('required')).toBeDefined()
    expect(passwordInput.attributes('required')).toBeDefined()
    expect(submitButton.attributes('type')).toBe('submit')
  })
})

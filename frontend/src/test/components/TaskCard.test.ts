import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import TaskCard from '@/components/TaskCard.vue'

describe('TaskCard', () => {
  let wrapper: any
  let pinia: any

  const mockTask = {
    id: 1,
    title: 'Test Task',
    description: 'Test Description',
    status: 'pending',
    due_date: '2024-12-31',
    user_id: 1,
    user: {
      id: 1,
      name: 'Test User',
      email: 'test@example.com'
    }
  }

  beforeEach(() => {
    pinia = createPinia()
    setActivePinia(pinia)
    
    wrapper = mount(TaskCard, {
      props: {
        task: mockTask
      },
      global: {
        plugins: [pinia],
      },
    })
  })

  it('renders task information correctly', () => {
    expect(wrapper.text()).toContain('Test Task')
    expect(wrapper.text()).toContain('Test Description')
    expect(wrapper.text()).toContain('Test User')
  })

  it('displays correct status badge', () => {
    const statusBadge = wrapper.find('[data-testid="status-badge"]')
    expect(statusBadge.exists()).toBe(true)
    expect(statusBadge.text()).toContain('pending')
  })

  it('displays due date in correct format', () => {
    const dueDate = wrapper.find('[data-testid="due-date"]')
    expect(dueDate.exists()).toBe(true)
    expect(dueDate.text()).toContain('2024-12-31')
  })

  it('shows different status badges for different statuses', async () => {
    await wrapper.setProps({
      task: { ...mockTask, status: 'completed' }
    })

    const statusBadge = wrapper.find('[data-testid="status-badge"]')
    expect(statusBadge.text()).toContain('completed')
  })

  it('emits edit event when edit button is clicked', async () => {
    const editButton = wrapper.find('[data-testid="edit-button"]')
    await editButton.trigger('click')

    expect(wrapper.emitted('edit')).toBeTruthy()
    expect(wrapper.emitted('edit')[0]).toEqual([mockTask])
  })

  it('emits delete event when delete button is clicked', async () => {
    const deleteButton = wrapper.find('[data-testid="delete-button"]')
    await deleteButton.trigger('click')

    expect(wrapper.emitted('delete')).toBeTruthy()
    expect(wrapper.emitted('delete')[0]).toEqual([mockTask])
  })

  it('shows edit and delete buttons for authorized users', () => {
    const editButton = wrapper.find('[data-testid="edit-button"]')
    const deleteButton = wrapper.find('[data-testid="delete-button"]')

    expect(editButton.exists()).toBe(true)
    expect(deleteButton.exists()).toBe(true)
  })

  it('hides edit and delete buttons for unauthorized users', async () => {
    await wrapper.setProps({
      task: mockTask,
      canEdit: false,
      canDelete: false
    })

    const editButton = wrapper.find('[data-testid="edit-button"]')
    const deleteButton = wrapper.find('[data-testid="delete-button"]')

    expect(editButton.exists()).toBe(false)
    expect(deleteButton.exists()).toBe(false)
  })

  it('applies correct CSS classes based on status', () => {
    const card = wrapper.find('[data-testid="task-card"]')
    expect(card.classes()).toContain('task-card')
    expect(card.classes()).toContain('status-pending')
  })

  it('handles missing description gracefully', async () => {
    await wrapper.setProps({
      task: { ...mockTask, description: null }
    })

    expect(wrapper.text()).toContain('Test Task')
    expect(wrapper.text()).not.toContain('Test Description')
  })

  it('handles missing due date gracefully', async () => {
    await wrapper.setProps({
      task: { ...mockTask, due_date: null }
    })

    const dueDate = wrapper.find('[data-testid="due-date"]')
    expect(dueDate.text()).toContain('Sin fecha')
  })

  it('formats due date correctly', () => {
    const dueDate = wrapper.find('[data-testid="due-date"]')
    expect(dueDate.text()).toContain('31/12/2024')
  })

  it('shows priority indicator for urgent tasks', async () => {
    const urgentDate = new Date()
    urgentDate.setDate(urgentDate.getDate() + 1) // Tomorrow

    await wrapper.setProps({
      task: { 
        ...mockTask, 
        due_date: urgentDate.toISOString().split('T')[0] 
      }
    })

    const priorityIndicator = wrapper.find('[data-testid="priority-indicator"]')
    expect(priorityIndicator.exists()).toBe(true)
  })

  it('applies correct styling for overdue tasks', async () => {
    const overdueDate = new Date()
    overdueDate.setDate(overdueDate.getDate() - 1) // Yesterday

    await wrapper.setProps({
      task: { 
        ...mockTask, 
        due_date: overdueDate.toISOString().split('T')[0],
        status: 'pending'
      }
    })

    const card = wrapper.find('[data-testid="task-card"]')
    expect(card.classes()).toContain('overdue')
  })

  it('shows user information correctly', () => {
    const userInfo = wrapper.find('[data-testid="user-info"]')
    expect(userInfo.text()).toContain('Test User')
  })

  it('handles long task titles with ellipsis', async () => {
    const longTitle = 'This is a very long task title that should be truncated with ellipsis to prevent layout issues'
    
    await wrapper.setProps({
      task: { ...mockTask, title: longTitle }
    })

    const titleElement = wrapper.find('[data-testid="task-title"]')
    expect(titleElement.classes()).toContain('truncate')
  })

  it('emits view event when card is clicked', async () => {
    const card = wrapper.find('[data-testid="task-card"]')
    await card.trigger('click')

    expect(wrapper.emitted('view')).toBeTruthy()
    expect(wrapper.emitted('view')[0]).toEqual([mockTask])
  })

  it('has proper accessibility attributes', () => {
    const card = wrapper.find('[data-testid="task-card"]')
    expect(card.attributes('role')).toBe('article')
    expect(card.attributes('tabindex')).toBe('0')
  })

  it('supports keyboard navigation', async () => {
    const card = wrapper.find('[data-testid="task-card"]')
    
    await card.trigger('keydown', { key: 'Enter' })
    expect(wrapper.emitted('view')).toBeTruthy()

    await card.trigger('keydown', { key: ' ' })
    expect(wrapper.emitted('view')).toHaveLength(2)
  })
})

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { DialogRoot, DialogPortal, DialogOverlay, DialogContent } from 'radix-vue'
import {
  Search,
  LayoutDashboard,
  Users,
  Handshake,
  Package,
  ShoppingCart,
  Plus,
  LogOut,
  Moon,
  Sun,
  Command,
  CornerDownLeft
} from 'lucide-vue-next'
import { useTheme } from '@/composables/useTheme'

const props = defineProps({
  persona: {
    type: String,
    default: 'admin', // 'admin' or 'employee'
  },
})

const isOpen = ref(false)
const searchQuery = ref('')
const selectedIndex = ref(0)
const inputRef = ref(null)

const { theme, toggleTheme } = useTheme()

// Command items based on persona (computed for reactive theme icon)
const adminCommands = computed(() => [
  { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, action: () => router.visit('/admin/dashboard'), category: 'Pages' },
  { id: 'users', label: 'User Management', icon: Users, action: () => router.visit('/admin/users'), category: 'Pages' },
  { id: 'partners', label: 'Partners', icon: Handshake, action: () => router.visit('/admin/partners'), category: 'Pages' },
  { id: 'box-templates', label: 'Box Templates', icon: Package, action: () => router.visit('/admin/box-templates'), category: 'Pages' },
  { id: 'create-user', label: 'Create User', icon: Plus, action: () => router.visit('/admin/users?action=create'), category: 'Actions' },
  { id: 'create-partner', label: 'Create Partner', icon: Plus, action: () => router.visit('/admin/partners?action=create'), category: 'Actions' },
  { id: 'toggle-theme', label: 'Toggle Theme', icon: theme.value === 'admin' ? Moon : Sun, action: () => toggleTheme(), category: 'Settings' },
  { id: 'logout', label: 'Logout', icon: LogOut, action: () => router.post('/logout'), category: 'Settings' },
])

const employeeCommands = computed(() => [
  { id: 'pos', label: 'POS / Kasir', icon: ShoppingCart, action: () => router.visit('/pos'), category: 'Pages' },
  { id: 'box-orders', label: 'Box Orders', icon: Package, action: () => router.visit('/pos/box'), category: 'Pages' },
  { id: 'create-box-order', label: 'New Box Order', icon: Plus, action: () => router.visit('/pos/box/create'), category: 'Actions' },
  { id: 'open-shop', label: 'Open Shop', icon: ShoppingCart, action: () => router.visit('/pos/open'), category: 'Actions' },
  { id: 'toggle-theme', label: 'Toggle Theme', icon: theme.value === 'employee' ? Moon : Sun, action: () => toggleTheme(), category: 'Settings' },
  { id: 'logout', label: 'Logout', icon: LogOut, action: () => router.post('/logout'), category: 'Settings' },
])

const commands = computed(() => props.persona === 'admin' ? adminCommands.value : employeeCommands.value)

// Filter commands based on search
const filteredCommands = computed(() => {
  if (!searchQuery.value.trim()) return commands.value
  const query = searchQuery.value.toLowerCase()
  return commands.value.filter(cmd => 
    cmd.label.toLowerCase().includes(query) ||
    cmd.category.toLowerCase().includes(query)
  )
})

// Group by category
const groupedCommands = computed(() => {
  const groups = {}
  filteredCommands.value.forEach(cmd => {
    if (!groups[cmd.category]) groups[cmd.category] = []
    groups[cmd.category].push(cmd)
  })
  return groups
})

// Keyboard navigation
const handleKeyDown = (e) => {
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    selectedIndex.value = Math.min(selectedIndex.value + 1, filteredCommands.value.length - 1)
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
  } else if (e.key === 'Enter') {
    e.preventDefault()
    const cmd = filteredCommands.value[selectedIndex.value]
    if (cmd) executeCommand(cmd)
  }
}

// Execute command
const executeCommand = (cmd) => {
  isOpen.value = false
  searchQuery.value = ''
  selectedIndex.value = 0
  cmd.action()
}

// Reset selection when search changes
watch(searchQuery, () => {
  selectedIndex.value = 0
})

// Global CMD+K listener
const handleGlobalKeyDown = (e) => {
  // CMD+K (Mac) or CTRL+K (Windows)
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    isOpen.value = !isOpen.value
  }
  // ESC to close
  if (e.key === 'Escape' && isOpen.value) {
    isOpen.value = false
  }
}

// Focus input when opened
watch(isOpen, (open) => {
  if (open) {
    searchQuery.value = ''
    selectedIndex.value = 0
    setTimeout(() => inputRef.value?.focus(), 50)
  }
})

onMounted(() => {
  document.addEventListener('keydown', handleGlobalKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleGlobalKeyDown)
})
</script>

<template>
  <DialogRoot v-model:open="isOpen">
    <DialogPortal>
      <!-- Backdrop with Glassmorphism -->
      <DialogOverlay 
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm animate-fade-in"
      />

      <!-- Command Palette Modal -->
      <DialogContent 
        class="fixed left-1/2 top-[20%] z-50 w-full max-w-lg -translate-x-1/2 animate-slide-in-down"
        @keydown="handleKeyDown"
      >
        <div class="glass rounded-2xl border border-white/20 shadow-2xl overflow-hidden">
          <!-- Search Input -->
          <div class="flex items-center gap-3 px-4 py-3 border-b border-white/10">
            <Search class="w-5 h-5 text-muted-foreground" />
            <input
              ref="inputRef"
              v-model="searchQuery"
              type="text"
              placeholder="Search commands..."
              class="flex-1 bg-transparent text-foreground placeholder:text-muted-foreground focus:outline-none text-sm"
            />
            <div class="flex items-center gap-1 text-xs text-muted-foreground">
              <kbd class="px-1.5 py-0.5 bg-muted/50 rounded text-xs">ESC</kbd>
            </div>
          </div>

          <!-- Commands List -->
          <div class="max-h-80 overflow-y-auto p-2">
            <!-- Empty State -->
            <div v-if="filteredCommands.length === 0" class="py-8 text-center text-muted-foreground text-sm">
              No commands found for "{{ searchQuery }}"
            </div>

            <!-- Grouped Commands -->
            <template v-for="(cmds, category) in groupedCommands" :key="category">
              <div class="px-2 py-1.5">
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                  {{ category }}
                </span>
              </div>
              <div
                v-for="(cmd, idx) in cmds"
                :key="cmd.id"
                @click="executeCommand(cmd)"
                @mouseenter="selectedIndex = filteredCommands.indexOf(cmd)"
                :class="[
                  'flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-all duration-150',
                  filteredCommands.indexOf(cmd) === selectedIndex
                    ? 'bg-primary/10 text-primary'
                    : 'text-foreground hover:bg-muted/50'
                ]"
              >
                <component :is="cmd.icon" class="w-4 h-4" />
                <span class="flex-1 text-sm">{{ cmd.label }}</span>
                <CornerDownLeft 
                  v-if="filteredCommands.indexOf(cmd) === selectedIndex"
                  class="w-3.5 h-3.5 text-primary"
                />
              </div>
            </template>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-between px-4 py-2.5 border-t border-white/10 bg-muted/30">
            <div class="flex items-center gap-4 text-xs text-muted-foreground">
              <span class="flex items-center gap-1.5">
                <kbd class="px-1.5 py-0.5 bg-muted rounded">↑</kbd>
                <kbd class="px-1.5 py-0.5 bg-muted rounded">↓</kbd>
                Navigate
              </span>
              <span class="flex items-center gap-1.5">
                <kbd class="px-1.5 py-0.5 bg-muted rounded">↵</kbd>
                Select
              </span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
              <Command class="w-3 h-3" />
              <span>K</span>
            </div>
          </div>
        </div>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>

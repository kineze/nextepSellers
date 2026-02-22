<template>
  <div class="p-6 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Inventory</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Category Manager</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Build and organize your multi-level category tree.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="relative w-full max-w-sm">
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
          <input
            v-model="search"
            type="text"
            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-8 text-sm text-slate-700 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
            placeholder="Search categories..."
          />
          <button
            v-if="search"
            @click="clearSearch"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
            title="Clear"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="openCreateRoot"
            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black dark:bg-white dark:text-slate-900"
          >
            <i class="fas fa-plus"></i> New Category
          </button>
          <button
            @click="fetchTree"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
          >
            Refresh
          </button>
        </div>
      </div>
    </div>

    <div class="mt-5 grid gap-4 lg:grid-cols-3">
      <div class="lg:col-span-2 rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/70 pb-3 dark:border-slate-800/70">
          <h3 class="text-sm font-semibold text-slate-700 dark:text-white">Category Tree</h3>
          <div class="flex items-center gap-2">
            <button
              @click="expandAll"
              class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            >
              Expand all
            </button>
            <button
              @click="collapseAll"
              class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            >
              Collapse
            </button>
          </div>
        </div>

        <div class="mt-4 space-y-2">
          <p v-if="filteredTree.length === 0" class="text-sm text-slate-500 dark:text-slate-400">
            No categories match your search.
          </p>
          <category-node
            v-for="category in filteredTree"
            :key="category.id"
            :category="category"
            :level="0"
            :expanded="expandedIds"
            @toggle="toggleExpand"
            @edit="startEdit"
            @create-child="openCreateChild"
            @delete="confirmDelete"
          />
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700 dark:text-white">
            {{ isEditing ? 'Edit Category' : 'Create Category' }}
          </h3>
          <button
            v-if="isEditing"
            @click="resetForm"
            class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
          >
            Clear
          </button>
        </div>

        <form @submit.prevent="saveCategory" class="mt-4 space-y-4">
          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
              placeholder="Furniture"
              required
            />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Slug (optional)</label>
            <input
              v-model="form.slug"
              type="text"
              class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
              placeholder="furniture"
            />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Parent</label>
            <select
              v-model="form.parent_id"
              class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
            >
              <option :value="null">No parent</option>
              <option
                v-for="option in parentOptions"
                :key="option.id"
                :value="option.id"
              >
                {{ option.label }}
              </option>
            </select>
          </div>

          <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" id="cat-active" v-model="form.is_active" class="rounded border-slate-300 dark:border-slate-700" />
            <label for="cat-active">Active</label>
          </div>

          <button
            type="submit"
            class="w-full rounded-xl bg-slate-900 py-2.5 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-black dark:bg-white dark:text-slate-900"
          >
            {{ isEditing ? 'Update Category' : 'Save Category' }}
          </button>
        </form>
      </div>
    </div>

    <transition name="fade">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-[990] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
      >
        <div class="w-full max-w-sm rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95 dark:text-white">
          <h3 class="text-lg font-semibold">Delete Category</h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Deleting a category will move its children to the root level. Continue?
          </p>
          <div class="mt-4 flex justify-end gap-3">
            <button @click="showDeleteModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
              Cancel
            </button>
            <button @click="deleteCategory" class="rounded-xl bg-rose-600 px-4 py-2 text-sm text-white hover:bg-rose-700">
              Delete
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";

const CategoryNode = {
  name: "CategoryNode",
  props: {
    category: { type: Object, required: true },
    level: { type: Number, required: true },
    expanded: { type: Set, required: true },
  },
  emits: ["toggle", "edit", "create-child", "delete"],
  computed: {
    hasChildren() {
      return this.category.children_recursive && this.category.children_recursive.length > 0;
    },
    isExpanded() {
      return this.expanded.has(this.category.id);
    },
  },
  template: `
    <div>
      <div
        class="flex items-center justify-between gap-2 rounded-xl border border-slate-200/70 bg-slate-50/90 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-100"
        :style="{ marginLeft: level * 16 + 'px' }"
      >
        <div class="flex items-center gap-2">
          <button
            v-if="hasChildren"
            @click="$emit('toggle', category.id)"
            class="text-xs text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-200"
            :title="isExpanded ? 'Collapse' : 'Expand'"
          >
            <i :class="isExpanded ? 'fas fa-chevron-down' : 'fas fa-chevron-right'"></i>
          </button>
          <span v-else class="w-4"></span>
          <span class="font-medium">{{ category.name }}</span>
          <span class="text-xs text-slate-400 dark:text-slate-500">/{{ category.slug }}</span>
          <span
            class="ml-2 rounded-full px-2 py-0.5 text-[10px]"
            :class="category.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
          >
            {{ category.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
          <button @click="$emit('create-child', category)" class="hover:text-slate-800 dark:hover:text-white" title="Add child">
            <i class="fas fa-plus"></i>
          </button>
          <button @click="$emit('edit', category)" class="hover:text-slate-800 dark:hover:text-white" title="Edit">
            <i class="fas fa-pen"></i>
          </button>
          <button @click="$emit('delete', category)" class="hover:text-rose-600" title="Delete">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
      <div v-if="hasChildren && isExpanded" class="mt-2 space-y-2">
        <category-node
          v-for="child in category.children_recursive"
          :key="child.id"
          :category="child"
          :level="level + 1"
          :expanded="expanded"
          @toggle="$emit('toggle', $event)"
          @edit="$emit('edit', $event)"
          @create-child="$emit('create-child', $event)"
          @delete="$emit('delete', $event)"
        />
      </div>
    </div>
  `,
};

export default {
  components: { CategoryNode },
  data() {
    return {
      tree: [],
      search: "",
      searchTimer: null,
      expandedIds: new Set(),
      isEditing: false,
      showDeleteModal: false,
      deletingCategory: null,
      form: {
        id: null,
        name: "",
        slug: "",
        parent_id: null,
        is_active: true,
      },
      toast: useToast(),
    };
  },
  computed: {
    filteredTree() {
      if (!this.search.trim()) return this.tree;
      const term = this.search.trim().toLowerCase();
      return this.filterTree(this.tree, term);
    },
    parentOptions() {
      const all = this.flattenTree(this.tree);
      if (!this.isEditing || !this.form.id) {
        return all;
      }
      const disallowed = new Set([this.form.id, ...this.descendantIds(this.form.id)]);
      return all.filter((option) => !disallowed.has(option.id));
    },
  },
  mounted() {
    this.fetchTree();
  },
  watch: {
    search() {
      if (this.searchTimer) clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => {
        if (this.search.trim()) {
          this.expandMatches();
        }
      }, 200);
    },
  },
  methods: {
    async fetchTree() {
      try {
        const res = await axios.get("/api/categories/tree");
        this.tree = res.data;
        if (this.search.trim()) {
          this.expandMatches();
        }
      } catch {
        this.toast.error("Failed to load categories.");
      }
    },
    flattenTree(tree, level = 0, acc = []) {
      tree.forEach((node) => {
        acc.push({
          id: node.id,
          label: `${"-".repeat(level * 2)} ${node.name}`,
        });
        if (node.children_recursive && node.children_recursive.length) {
          this.flattenTree(node.children_recursive, level + 1, acc);
        }
      });
      return acc;
    },
    filterTree(tree, term) {
      const result = [];
      tree.forEach((node) => {
        const nameMatch = node.name?.toLowerCase().includes(term);
        const slugMatch = node.slug?.toLowerCase().includes(term);
        const childMatches = node.children_recursive
          ? this.filterTree(node.children_recursive, term)
          : [];

        if (nameMatch || slugMatch || childMatches.length) {
          result.push({
            ...node,
            children_recursive: childMatches,
          });
        }
      });
      return result;
    },
    expandMatches() {
      const ids = new Set(this.expandedIds);
      const addAncestors = (nodes) => {
        nodes.forEach((node) => {
          if (node.children_recursive && node.children_recursive.length) {
            ids.add(node.id);
            addAncestors(node.children_recursive);
          }
        });
      };
      addAncestors(this.filteredTree);
      this.expandedIds = new Set(ids);
    },
    clearSearch() {
      this.search = "";
    },
    descendantIds(id) {
      const map = new Map();
      const buildMap = (nodes) => {
        nodes.forEach((node) => {
          map.set(node.id, node);
          if (node.children_recursive) {
            buildMap(node.children_recursive);
          }
        });
      };
      buildMap(this.tree);
      const root = map.get(id);
      const ids = [];
      const walk = (node) => {
        if (!node || !node.children_recursive) return;
        node.children_recursive.forEach((child) => {
          ids.push(child.id);
          walk(child);
        });
      };
      walk(root);
      return ids;
    },
    toggleExpand(id) {
      if (this.expandedIds.has(id)) {
        this.expandedIds.delete(id);
      } else {
        this.expandedIds.add(id);
      }
      this.expandedIds = new Set(this.expandedIds);
    },
    expandAll() {
      const allIds = this.flattenTree(this.tree).map((item) => item.id);
      this.expandedIds = new Set(allIds);
    },
    collapseAll() {
      this.expandedIds = new Set();
    },
    openCreateRoot() {
      this.resetForm();
      this.form.parent_id = null;
    },
    openCreateChild(category) {
      this.resetForm();
      this.form.parent_id = category.id;
    },
    startEdit(category) {
      this.isEditing = true;
      this.form = {
        id: category.id,
        name: category.name,
        slug: category.slug,
        parent_id: category.parent_id,
        is_active: !!category.is_active,
      };
    },
    resetForm() {
      this.isEditing = false;
      this.form = {
        id: null,
        name: "",
        slug: "",
        parent_id: null,
        is_active: true,
      };
    },
    async saveCategory() {
      try {
        const payload = {
          name: this.form.name,
          slug: this.form.slug || null,
          parent_id: this.form.parent_id || null,
          is_active: this.form.is_active ? 1 : 0,
        };

        if (this.isEditing && this.form.id) {
          await axios.put(`/api/categories/${this.form.id}`, payload);
          this.toast.success("Category updated.");
        } else {
          await axios.post("/api/categories", payload);
          this.toast.success("Category created.");
        }
        await this.fetchTree();
        this.resetForm();
      } catch (error) {
        const message = error?.response?.data?.message
          || Object.values(error?.response?.data?.errors || {}).flat()[0]
          || "Failed to save category.";
        this.toast.error(message);
      }
    },
    confirmDelete(category) {
      this.deletingCategory = category;
      this.showDeleteModal = true;
    },
    async deleteCategory() {
      if (!this.deletingCategory) return;
      const deletedId = this.deletingCategory.id;
      try {
        await axios.delete(`/api/categories/${this.deletingCategory.id}`);
        this.toast.success("Category deleted.");
        this.showDeleteModal = false;
        this.deletingCategory = null;
        await this.fetchTree();
        if (this.isEditing && this.form.id === deletedId) {
          this.resetForm();
        }
      } catch {
        this.toast.error("Failed to delete category.");
      }
    },
  },
};
</script>

<script setup lang="ts">
import FormField from '@/Components/dashboard/FormField.vue';
import PageHeader from '@/Components/dashboard/PageHeader.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { NativeSelect } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppShell from '@/Layouts/AppShell.vue';
import { slugify } from '@/lib/slug';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
}

defineProps<{ categories: Category[] }>();

const slugTouched = ref(false);

const form = useForm({
    title: '',
    slug: '',
    category_id: '' as number | '',
    pricing_type: 'free' as 'free' | 'paid',
    price: '' as string | number,
    price_minor: 0,
    short_description: '',
    description: '',
});

function onTitleInput() {
    if (!slugTouched.value) {
        form.slug = slugify(form.title);
    }
}

function submit() {
    form.transform((data) => ({
        ...data,
        category_id: data.category_id || null,
        pricing_type: data.pricing_type,
        price_minor:
            data.pricing_type === 'paid'
                ? Math.round(Number(data.price || 0) * 100)
                : 0,
        currency: 'USD',
    })).post(route('teacher.courses.store'));
}
</script>

<template>
    <Head title="Create Course" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">New Course</h2>
        </template>

        <PageHeader
            title="Create a course"
            description="Start with the basics — you can flesh out the curriculum afterwards."
        />

        <Card class="max-w-2xl">
            <CardContent class="pt-6">
                <form class="space-y-5" @submit.prevent="submit">
                    <FormField
                        label="Title"
                        for="title"
                        :error="form.errors.title"
                    >
                        <Input
                            id="title"
                            v-model="form.title"
                            placeholder="e.g. Laravel From Scratch"
                            @input="onTitleInput"
                        />
                    </FormField>

                    <FormField
                        label="Slug"
                        for="slug"
                        :error="form.errors.slug"
                        hint="Used in the course URL. Auto-filled from the title, but you can edit it."
                    >
                        <Input
                            id="slug"
                            v-model="form.slug"
                            @input="slugTouched = true"
                        />
                    </FormField>

                    <FormField
                        label="Category"
                        for="category_id"
                        :error="form.errors.category_id"
                    >
                        <NativeSelect
                            id="category_id"
                            v-model="form.category_id"
                        >
                            <option value="">No category</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </NativeSelect>
                    </FormField>

                    <FormField
                        label="Short description"
                        for="short_description"
                        :error="form.errors.short_description"
                    >
                        <Input
                            id="short_description"
                            v-model="form.short_description"
                            placeholder="One sentence describing the course"
                        />
                    </FormField>

                    <FormField
                        label="Description"
                        for="description"
                        :error="form.errors.description"
                    >
                        <Textarea
                            id="description"
                            v-model="form.description"
                            :rows="5"
                        />
                    </FormField>

                    <FormField
                        label="Pricing"
                        for="pricing_type"
                        :error="form.errors.pricing_type"
                    >
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    value="free"
                                    v-model="form.pricing_type"
                                />
                                Free
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    value="paid"
                                    v-model="form.pricing_type"
                                />
                                Paid
                            </label>
                        </div>
                    </FormField>

                    <FormField
                        v-if="form.pricing_type === 'paid'"
                        label="Price (USD)"
                        for="price"
                        :error="form.errors.price_minor"
                    >
                        <Input
                            id="price"
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="49.99"
                        />
                    </FormField>

                    <div class="flex items-center gap-3 pt-2">
                        <Button type="submit" :disabled="form.processing"
                            >Create course</Button
                        >
                        <Link
                            :href="route('teacher.courses.index')"
                            class="text-muted-foreground text-sm hover:underline"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AppShell>
</template>

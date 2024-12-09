<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    //role_type: "",
});

/*
const roleTypes = ref([]);

const fetchRoleTypes = async () => {
    try {
        const response = await axios.get("/role-types");
        roleTypes.value = response.data;
    } catch (error) {
        console.error("Error fetching role types:", error);
    }
};

onMounted(() => {
    fetchRoleTypes();
});
*/

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <div class="bg-[#12bdee] flex items-center justify-center min-h-screen">
        <div class="bg-white shadow-md rounded-lg flex max-w-4xl w-full">
            <div class="w-1/2 p-8 flex flex-col items-center justify-center">
                <img
                    src="/images/barunawati.webp"
                    class="w-2/3 h-auto object-contain mb-4"
                    alt="Gambar Barunawati"
                />
                <h2 class="text-2xl font-bold text-center">
                    SMA BARUNAWATI SURABAYA
                </h2>
            </div>
            <div class="w-1/2 p-8">
                <h2 class="text-2xl font-bold text-center">SELAMAT DATANG</h2>
                <Link
                    class="text-center text-gray-500 mb-6"
                    :href="route('login')"
                >
                    <p class="text-blue-500">Sign In</p>
                </Link>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <InputLabel
                            for="name"
                            class="block text-gray-700 mb-2"
                            value="Nama Lengkap"
                        />
                        <TextInput
                            id="name"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="FullName"
                            placeholder="Masukkan Nama Lengkap"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="mb-4">
                        <InputLabel
                            for="email"
                            class="block text-gray-700 mb-2"
                            value="Email"
                        />
                        <TextInput
                            id="email"
                            type="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Masukkan Email"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- 
                                        <div class="mb-4">
                        <InputLabel
                            for="role_type"
                            class="block text-gray-700 mb-2"
                            value="Role Type"
                        />
                        <select
                            id="role_type"
                            v-model="form.role_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                            <option value="" disabled>Pilih Role Type</option>
                            <option
                                v-for="role in roleTypes"
                                :key="role.id"
                                :value="role.role_type"
                            >
                                {{ role.role_type }}
                            </option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="form.errors.role_type"
                        />
                    </div>

                    -->

                    <div class="mb-4">
                        <InputLabel
                            for="password"
                            class="block text-gray-700 mb-2"
                            value="Password"
                        />
                        <TextInput
                            id="password"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan Password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>

                    <div class="mb-4">
                        <InputLabel
                            for="password_confirmation"
                            class="block text-gray-700 mb-2"
                            value="Konfirmasi Password"
                        />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan Password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>

                    <PrimaryButton
                        class="w-full px-3 py-2 rounded-lg bg-[#12bdee] items-center justify-center"
                        style="text-align: center; text-transform: none"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Register
                    </PrimaryButton>
                </form>
            </div>
        </div>
    </div>
</template>

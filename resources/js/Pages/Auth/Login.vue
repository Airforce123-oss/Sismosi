<script setup>
import { ref } from "vue";
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const userName = ref("");

const fetchSessionData = async () => {
    try {
        const response = await axios.get("/api/session-data");
        userName.value = response.data.name;
    } catch (error) {
        console.error("There was an error fetching the session data:", error);
    }
};

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <!-- --->
    <div class="bg-[#9CF09C] flex items-center justify-center min-h-screen">
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
                <p class="text-center text-gray-500 mb-6">
                    <a href="register" class="text-blue-500">Sign Up</a>
                </p>
                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700"
                            >Email</label
                        >
                        <TextInput
                            id="email"
                            type="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Masukkan Email"
                        />
                        <!-- mt-1 block w-full -->
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700"
                            >Password</label
                        >
                        <TextInput
                            id="password"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan Password"
                        />
                        <!-- mt-1 block w-full -->
                    </div>
                    <PrimaryButton
                        class="w-full px-3 py-2 rounded-lg bg-green-400 items-center justify-center"
                        style="text-align: center; text-transform: none"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Login
                    </PrimaryButton>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { initFlowbite } from "flowbite";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import VueApexCharts from "vue-apexcharts";
import ApexCharts from "apexcharts";
import axios from "axios";

const userName = ref("");
const { props } = usePage();
const form = useForm({
    name: props.auth.user.name,
    email: props.auth.user.email,
    role_type: props.auth.user.role_type,
});

onMounted(() => {
    initFlowbite();

    

    // Line Chart Options
    const lineChartOptions = {
        chart: { height: 300, type: "line", toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: "smooth" },
        series: [
            {
                name: "Guru",
                color: "#3D5EE1",
                data: [45, 60, 75, 51, 42, 42, 30],
            },
            {
                name: "Siswa",
                color: "#70C4CF",
                data: [24, 48, 56, 32, 34, 52, 25],
            },
        ],
        xaxis: {
            categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"],
        },
    };
    const lineChart = new ApexCharts(
        document.querySelector("#apexcharts-area"),
        lineChartOptions
    );
    lineChart.render();

    // Bar Chart Options
    const barChartOptions = {
        chart: {
            type: "bar",
            height: 300,
            width: "100%",
            stacked: false,
            toolbar: { show: false },
        },
        dataLabels: { enabled: false },
        plotOptions: {
            bar: { columnWidth: "55%", endingShape: "rounded" },
        },
        stroke: { show: true, width: 2, colors: ["transparent"] },
        series: [
            {
                name: "Laki-laki",
                color: "#70C4CF",
                data: [
                    420, 532, 516, 575, 519, 517, 454, 392, 262, 383, 446, 551,
                ],
            },
            {
                name: "Perempuan",
                color: "#3D5EE1",
                data: [
                    336, 612, 344, 647, 345, 563, 256, 344, 323, 300, 455, 456,
                ],
            },
        ],
        xaxis: {
            categories: [
                2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018,
                2019, 2020,
            ],
            labels: { show: false },
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: {
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: "#777" } },
        },
        title: { text: "", align: "left", style: { fontSize: "18px" } },
    };
    const barChart = new ApexCharts(
        document.querySelector("#bar"),
        barChartOptions
    );
    barChart.render();
    /*
      const fetchSessionData = async () => {
        try {
            const response = await axios.get("/api/session-data");
            userName.value = response.data.name;
        } catch (error) {
            console.error(
                "There was an error fetching the session data:",
                error
            );
        }
    };

    fetchSessionData();
     */
});
</script>

<template>
    <div class="antialiased bg-gray-50 dark:bg-gray-900">
        <nav
            class="bg-white border-b border-gray-200 px-4 py-2.5 dark:bg-gray-800 dark:border-gray-700 fixed left-0 right-0 top-0 z-50"
        >
            <div class="flex flex-wrap justify-between items-center">
                <div class="flex justify-start items-center">
                    <button
                        data-drawer-target="drawer-navigation"
                        data-drawer-toggle="drawer-navigation"
                        aria-controls="drawer-navigation"
                        class="p-2 mr-2 text-gray-600 rounded-lg cursor-pointer md:hidden hover:text-gray-900 hover:bg-gray-100 focus:bg-gray-100 dark:focus:bg-gray-700 focus:ring-2 focus:ring-gray-100 dark:focus:ring-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <svg
                            class="hidden w-6 h-6"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <span class="sr-only">Toggle sidebar</span>
                    </button>
                    <a href="" class="flex items-center justify-between mr-4">
                        <img
                            src="/images/barunawati.jpeg"
                            class="mr-3 h-8"
                            alt=""
                        />
                        <span
                            class="self-center text-base md:text-lg lg:text-xl xl:text-2xl font-semibold whitespace-nowrap dark:text-white"
                            >SMA BARUNAWATI SURABAYA</span
                        >
                    </a>
                </div>
                <div class="flex items-center lg:order-2">
                    <!--
                                        <button
                        type="button"
                        data-drawer-toggle="drawer-navigation"
                        aria-controls="drawer-navigation"
                        class="p-2 mr-1 text-gray-500 rounded-lg md:hidden hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                    >
                        <span class="sr-only">Toggle search</span>
                        <svg
                            class="w-6 h-6"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                clip-rule="evenodd"
                                fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            ></path>
                        </svg>
                    </button>
                    -->
                    <!-- Apps -->
                    <button
                        type="button"
                        class="p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                    ></button>

                    <button
                        type="button"
                        class="flex mx-3 text-sm rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                        id="user-menu-button"
                        aria-expanded="false"
                        data-dropdown-toggle="dropdown"
                    >
                        <span class="sr-only">Open user menu</span>
                        <svg
                            baseProfile="tiny"
                            height="24px"
                            id="Layer_1"
                            version="1.2"
                            viewBox="0 0 24 24"
                            width="24px"
                            xml:space="preserve"
                            xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink"
                        >
                            <path
                                d="M12,3c0,0-6.186,5.34-9.643,8.232C2.154,11.416,2,11.684,2,12c0,0.553,0.447,1,1,1h2v7c0,0.553,0.447,1,1,1h3  c0.553,0,1-0.448,1-1v-4h4v4c0,0.552,0.447,1,1,1h3c0.553,0,1-0.447,1-1v-7h2c0.553,0,1-0.447,1-1c0-0.316-0.154-0.584-0.383-0.768  C18.184,8.34,12,3,12,3z"
                                fill="black"
                            />
                        </svg>
                    </button>
                    <!-- Dropdown menu -->
                    <div
                        class="hidden w-full sm:w-1/2 lg:w-1/4 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
                        id="dropdown"
                    >
                        <div class="py-3 px-3">
                            <div
                                class="'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base text-indigo-700 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out text-[12px]'"
                            >
                                <span
                                    class="block text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{ $page.props.auth.user.email }}
                                </span>
                                <span
                                    class="block text-sm text-gray-900 truncate dark:text-white"
                                >
                                    {{ $page.props.auth.user.name }}
                                </span>
                                <span
                                    class="block text-sm text-gray-900 truncate dark:text-white"
                                    >{{ form.role_type }}</span
                                >
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil Saya
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main -->

        <main class="p-7 md:ml-64 h-screen pt-20">
            <Head title="Dashboard" />

            <div class="text-2xl col-sm-12 mb-10">
                <div class="page-sub-header">
                    <h3 class="page-title">
                        Selamat Datang {{ $page.props.auth.user.name }}!
                    </h3>
                </div>
            </div>

            <div
                class="flex justify-center items-center grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 mb-4"
            >
                <div
                    class="border-2 border-solid border-gray-300 bg-[#8ec3b3] rounded-lg dark:border-gray-600 h-15 md:h-34"
                    id=""
                >
                    <h2 class="text-center text-[#ffffff]">Jumlah Siswa</h2>
                    <p
                        class="output text-center mt-5 text-xl text-[#ffffff]"
                        placeholder=""
                    >
                        509
                    </p>
                </div>

                <div
                    class="border-2 border-solid border-gray-300 bg-[#8ec3b3] rounded-lg dark:border-gray-600 h-15 md:h-34"
                >
                    <h2 class="text-center text-[#ffffff]">Jumlah Kelas</h2>
                    <p
                        class="output text-center mt-5 text-xl text-[#ffffff]"
                        placeholder=""
                    >
                        16
                    </p>
                </div>
                <div
                    class="border-2 border-solid border-gray-300 bg-[#8ec3b3] rounded-lg dark:border-gray-600 h-15 md:h-34"
                >
                    <h2 class="text-center text-[#ffffff]">Jumlah Pengguna</h2>
                    <p
                        class="output text-center mt-5 text-xl text-[#ffffff]"
                        placeholder=""
                    >
                        0
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <div class="card card-chart">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <h5 class="card-title">Overview</h5>
                                </div>
                                <div class="col-6">
                                    <ul class="chart-list-out">
                                        <li>
                                            <span class="circle-blue"></span
                                            >Guru
                                        </li>
                                        <li>
                                            <span class="circle-green"></span
                                            >Siswa
                                        </li>
                                        <li class="star-menus">
                                            <a href="javascript:;"
                                                ><i
                                                    class="fas fa-ellipsis-v"
                                                ></i
                                            ></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="apexcharts-area"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-lg-6">
                    <div class="card card-chart">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <h5 class="card-title">Jumlah Siswa</h5>
                                </div>
                                <div class="col-6">
                                    <ul class="chart-list-out">
                                        <li>
                                            <span class="circle-blue"></span
                                            >Perempuan
                                        </li>
                                        <li>
                                            <span class="circle-green"></span
                                            >Laki-laki
                                        </li>
                                        <li class="star-menus">
                                            <a href="javascript:;"
                                                ><i
                                                    class="fas fa-ellipsis-v"
                                                ></i
                                            ></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="bar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--
             <p>
                Catatan: Melihat data siswa, mengelola data siswa, melihat data
                guru, mengelola data guru, melihat presensi siswa, mengelola
                presensi siswa, melihat presensi guru mengelola presensi guru,
                melihat mata pelajaran, mengelola mata pelajaran,
            </p>
            -->
        </main>
        <!-- Sidebar -->

        <aside
            class="fixed top-0 left-0 z-40 w-60 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-900"
            aria-label="Sidenav"
            id="drawer-navigation"
            style=""
        >
            <div
                class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800"
            >
                <ul class="space-y-2">
                    <li>
                        <a
                            href="dashboard"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                        >
                            <svg
                                baseProfile="tiny"
                                height="24px"
                                id="Layer_1"
                                version="1.2"
                                viewBox="0 0 24 24"
                                width="24px"
                                xml:space="preserve"
                                xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink"
                            >
                                <path
                                    d="M12,3c0,0-6.186,5.34-9.643,8.232C2.154,11.416,2,11.684,2,12c0,0.553,0.447,1,1,1h2v7c0,0.553,0.447,1,1,1h3  c0.553,0,1-0.448,1-1v-4h4v4c0,0.552,0.447,1,1,1h3c0.553,0,1-0.447,1-1v-7h2c0.553,0,1-0.447,1-1c0-0.316-0.154-0.584-0.383-0.768  C18.184,8.34,12,3,12,3z"
                                />
                            </svg>
                            <span class="ml-3">Beranda</span>
                        </a>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                            aria-controls="dropdown-pages"
                            data-collapse-toggle="dropdown-pages"
                            aria-expanded="true"
                        >
                            <svg
                                viewBox="0 0 256 256"
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                            >
                                <rect fill="none" height="256" width="256" />
                                <path
                                    d="M226.5,56.4l-96-32a8.5,8.5,0,0,0-5,0l-95.9,32h-.2l-1,.5h-.1l-1,.6c0,.1-.1.1-.2.2l-.8.7h0l-.7.8c0,.1-.1.1-.1.2l-.6.9c0,.1,0,.1-.1.2l-.4.9h0l-.3,1.1v.3A3.7,3.7,0,0,0,24,64v80a8,8,0,0,0,16,0V75.1L73.6,86.3A63.2,63.2,0,0,0,64,120a64,64,0,0,0,30,54.2,96.1,96.1,0,0,0-46.5,37.4,8.1,8.1,0,0,0,2.4,11.1,7.9,7.9,0,0,0,11-2.3,80,80,0,0,1,134.2,0,8,8,0,0,0,6.7,3.6,7.5,7.5,0,0,0,4.3-1.3,8.1,8.1,0,0,0,2.4-11.1A96.1,96.1,0,0,0,162,174.2,64,64,0,0,0,192,120a63.2,63.2,0,0,0-9.6-33.7l44.1-14.7a8,8,0,0,0,0-15.2ZM128,168a48,48,0,0,1-48-48,48.6,48.6,0,0,1,9.3-28.5l36.2,12.1a8,8,0,0,0,5,0l36.2-12.1A48.6,48.6,0,0,1,176,120,48,48,0,0,1,128,168Z"
                                />
                            </svg>

                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap"
                                >Siswa</span
                            >
                            <svg
                                class="w-6 h-6"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </button>
                        <ul id="dropdown-pages" class="hidden py-2 space-y-2">
                            <li>
                                <a
                                    href="students"
                                    class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                                    >Data Induk Siswa</a
                                >
                            </li>
                        </ul>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                            aria-controls="dropdown-pages1"
                            data-collapse-toggle="dropdown-pages1"
                        >
                            <svg
                                viewBox="0 0 640 512"
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                            >
                                <path
                                    d="M208 352c-2.39 0-4.78.35-7.06 1.09C187.98 357.3 174.35 360 160 360c-14.35 0-27.98-2.7-40.95-6.91-2.28-.74-4.66-1.09-7.05-1.09C49.94 352-.33 402.48 0 464.62.14 490.88 21.73 512 48 512h224c26.27 0 47.86-21.12 48-47.38.33-62.14-49.94-112.62-112-112.62zm-48-32c53.02 0 96-42.98 96-96s-42.98-96-96-96-96 42.98-96 96 42.98 96 96 96zM592 0H208c-26.47 0-48 22.25-48 49.59V96c23.42 0 45.1 6.78 64 17.8V64h352v288h-64v-64H384v64h-76.24c19.1 16.69 33.12 38.73 39.69 64H592c26.47 0 48-22.25 48-49.59V49.59C640 22.25 618.47 0 592 0z"
                                />
                            </svg>

                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap"
                                >Guru</span
                            >
                            <svg
                                class="w-6 h-6"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </button>
                        <ul id="dropdown-pages1" class="hidden py-2 space-y-2">
                            <li>
                                <a
                                    href="teachers"
                                    class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                                    >Data Induk Guru</a
                                >
                            </li>
                        </ul>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                            aria-controls="dropdown-sales"
                            data-collapse-toggle="dropdown-sales"
                        >
                            <svg
                                id="Icons_Teacher"
                                overflow="hidden"
                                version="1.1"
                                viewBox="0 0 96 96"
                                xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink"
                                class="w-5 h-5"
                            >
                                <path
                                    d=" M 87.8 19 L 23.8 19 C 21.6 19 19.8 20.8 19.8 23 L 19.8 37.5 C 20.9 37.2 22.2 37 23.4 37 C 24.2 37 25 37.1 25.8 37.2 L 25.8 25 L 85.8 25 L 85.8 63 L 51.9 63 L 46.2 69 L 87.8 69 C 90 69 91.8 67.2 91.8 65 L 91.8 23 C 91.8 20.8 90 19 87.8 19"
                                />
                                <path
                                    d=" M 23.5 58 C 28.2 58 32 54.2 32 49.5 C 32 44.8 28.2 41 23.5 41 C 18.8 41 15 44.8 15 49.5 C 14.9 54.2 18.8 58 23.5 58"
                                />
                                <path
                                    d=" M 56.2 48.1 C 54.9 46.1 52.3 45.6 50.3 46.8 C 49.9 47 49.7 47.4 49.5 47.6 L 34.9 62.8 C 33.5 62.1 32 61.5 30.5 61 C 28.2 60.6 25.8 60.1 23.5 60.1 C 21.2 60.1 18.8 60.5 16.5 61.2 C 13.1 62.1 10.1 63.8 7.6 65.9 C 7 66.5 6.5 67.4 6.3 68.2 L 4.2 77 L 34.1 77 L 34.1 76.9 L 42.6 67 L 55.7 53.2 C 56.9 52 57.3 49.7 56.2 48.1"
                                />
                            </svg>
                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap"
                                >Kelas</span
                            >
                            <svg
                                class="w-6 h-6"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </button>
                        <ul id="dropdown-sales" class="hidden py-2 space-y-2">
                            <li>
                                <a
                                    href="kelas"
                                    class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                                    >Membuat Kelas</a
                                >
                            </li>
                        </ul>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                            aria-controls="dropdown-authentication"
                            data-collapse-toggle="dropdown-authentication1"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="w-5 h-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M9.664 1.319a.75.75 0 0 1 .672 0 41.059 41.059 0 0 1 8.198 5.424.75.75 0 0 1-.254 1.285 31.372 31.372 0 0 0-7.86 3.83.75.75 0 0 1-.84 0 31.508 31.508 0 0 0-2.08-1.287V9.394c0-.244.116-.463.302-.592a35.504 35.504 0 0 1 3.305-2.033.75.75 0 0 0-.714-1.319 37 37 0 0 0-3.446 2.12A2.216 2.216 0 0 0 6 9.393v.38a31.293 31.293 0 0 0-4.28-1.746.75.75 0 0 1-.254-1.285 41.059 41.059 0 0 1 8.198-5.424ZM6 11.459a29.848 29.848 0 0 0-2.455-1.158 41.029 41.029 0 0 0-.39 3.114.75.75 0 0 0 .419.74c.528.256 1.046.53 1.554.82-.21.324-.455.63-.739.914a.75.75 0 1 0 1.06 1.06c.37-.369.69-.77.96-1.193a26.61 26.61 0 0 1 3.095 2.348.75.75 0 0 0 .992 0 26.547 26.547 0 0 1 5.93-3.95.75.75 0 0 0 .42-.739 41.053 41.053 0 0 0-.39-3.114 29.925 29.925 0 0 0-5.199 2.801 2.25 2.25 0 0 1-2.514 0c-.41-.275-.826-.541-1.25-.797a6.985 6.985 0 0 1-1.084 3.45 26.503 26.503 0 0 0-1.281-.78A5.487 5.487 0 0 0 6 12v-.54Z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap"
                                >Mata Pelajaran</span
                            >
                            <svg
                                class="w-6 h-6"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </button>

                        <ul
                            id="dropdown-authentication1"
                            class="hidden py-2 space-y-2"
                        >
                            <li>
                                <a
                                    href="mataPelajaran"
                                    class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                                    >Tambah Mata Pelajaran</a
                                >
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a
                            href="penilaian"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                        >
                            <svg
                                fill="none"
                                height="24"
                                viewBox="0 0 24 24"
                                width="24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M6 6C6 5.44772 6.44772 5 7 5H17C17.5523 5 18 5.44772 18 6C18 6.55228 17.5523 7 17 7H7C6.44771 7 6 6.55228 6 6Z"
                                    fill="currentColor"
                                />
                                <path
                                    d="M6 10C6 9.44771 6.44772 9 7 9H17C17.5523 9 18 9.44771 18 10C18 10.5523 17.5523 11 17 11H7C6.44771 11 6 10.5523 6 10Z"
                                    fill="currentColor"
                                />
                                <path
                                    d="M7 13C6.44772 13 6 13.4477 6 14C6 14.5523 6.44771 15 7 15H17C17.5523 15 18 14.5523 18 14C18 13.4477 17.5523 13 17 13H7Z"
                                    fill="currentColor"
                                />
                                <path
                                    d="M6 18C6 17.4477 6.44772 17 7 17H11C11.5523 17 12 17.4477 12 18C12 18.5523 11.5523 19 11 19H7C6.44772 19 6 18.5523 6 18Z"
                                    fill="currentColor"
                                />
                                <path
                                    clip-rule="evenodd"
                                    d="M2 4C2 2.34315 3.34315 1 5 1H19C20.6569 1 22 2.34315 22 4V20C22 21.6569 20.6569 23 19 23H5C3.34315 23 2 21.6569 2 20V4ZM5 3H19C19.5523 3 20 3.44771 20 4V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V4C4 3.44772 4.44771 3 5 3Z"
                                    fill="currentColor"
                                    fill-rule="evenodd"
                                />
                            </svg>
                            <span class="ml-3">Penilaian Siswa</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { initFlowbite } from "flowbite";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import VueApexCharts from "vue-apexcharts";
import ApexCharts from "apexcharts";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import $ from "jquery";
//import "@/assets/plugins/jquery.simple-calendar.js";
//import "@assets/js/bootstrap-datetimepicker.min.js";
//import "@assets/plugins/simple-calendar/jquery.simple-calendar.js";
import "@assets/plugins/simple-calendar/jquery.simple-calendar.js";
import "@assets/plugins/simple-calendar/simple-calendar.css";

const userName = ref("");
const { props } = usePage();
const form = useForm({
    name: props.auth.user.name,
    email: props.auth.user.email,
    role_type: props.auth.user.role_type,
});

onMounted(() => {
    initFlowbite();

    // Inisialisasi ApexCharts
    var options = {
        chart: { height: 350, type: "line", toolbar: { show: false } },
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
    var chart = new ApexCharts(
        document.querySelector("#apexcharts-area"),
        options
    );
    chart.render();

    //setTimeout(initCalendar, 1000); // Adjust the delay as needed

    // calendar trial
    const daysContainer = document.getElementById("days");
    const monthYearDisplay = document.getElementById("monthYear");
    const prevButton = document.getElementById("prev");
    const nextButton = document.getElementById("next");

    const months = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];
    let currentDate = new Date();

    function renderCalendar() {
        daysContainer.innerHTML = "";
        monthYearDisplay.textContent = `${
            months[currentDate.getMonth()]
        } ${currentDate.getFullYear()}`;

        const firstDayOfMonth = new Date(
            currentDate.getFullYear(),
            currentDate.getMonth(),
            1
        ).getDay();
        const daysInMonth = new Date(
            currentDate.getFullYear(),
            currentDate.getMonth() + 1,
            0
        ).getDate();

        for (let i = 0; i < firstDayOfMonth; i++) {
            daysContainer.appendChild(document.createElement("div"));
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement("div");
            dayElement.textContent = day;
            dayElement.classList.add(
                "flex",
                "items-center",
                "justify-center",
                "w-12",
                "h-12"
            );

            if (
                day === currentDate.getDate() &&
                currentDate.getMonth() === new Date().getMonth() &&
                currentDate.getFullYear() === new Date().getFullYear()
            ) {
                dayElement.classList.add(
                    "bg-blue-500",
                    "text-white",
                    "rounded-full"
                );
            }

            daysContainer.appendChild(dayElement);
        }
    }

    prevButton.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextButton.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    renderCalendar();
    //end calendar trial
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
                    <div>
                        <h3 class="page-title">
                            Selamat Datang {{ $page.props.auth.user.name }}!
                        </h3>
                    </div>
                </div>
            </div>
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"
            >
                <div
                    class="border-2 border-solid border-gray-300 bg-[#8ec3b3] rounded-lg dark:border-gray-600 h-15 md:h-34"
                    id=""
                >
                    <h2 class="text-center">Total Kelas</h2>
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
                    <h2 class="text-center">Total Siswa</h2>
                    <p
                        class="output text-center mt-5 text-xl text-[#ffffff]"
                        placeholder=""
                    >
                        519
                    </p>
                </div>
                <div
                    class="border-2 border-solid border-gray-300 bg-[#8ec3b3] rounded-lg dark:border-gray-600 h-15 md:h-34"
                >
                    <h2 class="text-center">Total Mata Pelajaran</h2>
                    <p
                        class="output text-center mt-5 text-xl text-[#ffffff]"
                        placeholder=""
                    >
                        16
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-16 col-xl-6">
                    <div class="card flex-fill comman-shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <h5 class="card-title">Today’s Lesson</h5>
                                </div>
                            </div>
                        </div>
                        <div class="dash-circle">
                            <div class="row">
                                <div class="col-lg-3 col-md-3">
                                    <div class="dash-details">
                                        <div class="lesson-activity">
                                            <div class="lesson-imgs">
                                                <!-- -->
                                                <!--   <img
                                                    src="{{URL::to('assets/img/icons/lesson-icon-01.svg')}}"
                                                    alt=""
                                                /> -->
                                            </div>
                                            <div class="views-lesson">
                                                <h5>Class</h5>
                                                <h4>Electrical Engg</h4>
                                            </div>
                                        </div>
                                        <div class="lesson-activity">
                                            <div class="lesson-imgs">
                                                <!--    <img
                                                    src="{{URL::to('assets/img/icons/lesson-icon-02.svg')}}"
                                                    alt=""
                                                /> -->
                                            </div>
                                            <div class="views-lesson">
                                                <h5>Lessons</h5>
                                                <h4>5 Lessons</h4>
                                            </div>
                                        </div>
                                        <div class="lesson-activity">
                                            <div class="lesson-imgs">
                                                <!--<img
                                                    src="{{URL::to('assets/img/icons/lesson-icon-03.svg')}}"
                                                    alt=""
                                                /> -->
                                            </div>
                                            <div class="views-lesson">
                                                <h5>Time</h5>
                                                <h4>Lessons</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3">
                                    <div class="dash-details">
                                        <div class="lesson-activity">
                                            <div class="lesson-imgs">
                                                <!--
                                                <img
                                                    src="{{URL::to('assets/img/icons/lesson-icon-04.svg')}}"
                                                    alt=""
                                                /> -->
                                            </div>
                                            <div class="views-lesson">
                                                <h5>Asignment</h5>
                                                <h4>5 Asignment</h4>
                                            </div>
                                        </div>
                                        <div class="lesson-activity">
                                            <div class="lesson-imgs">
                                                <!--
                                                <img
                                                    src="{{URL::to('assets/img/icons/lesson-icon-05.svg')}}"
                                                    alt=""
                                                /> -->
                                            </div>
                                            <div class="views-lesson">
                                                <h5>Staff</h5>
                                                <h4>John Doe</h4>
                                            </div>
                                        </div>
                                        <div class="lesson-activity">
                                            <div class="lesson-imgs">
                                                <!--
                                                <img
                                                    src="{{URL::to('assets/img/icons/lesson-icon-06.svg')}}"
                                                    alt=""
                                                /> -->
                                            </div>
                                            <div class="views-lesson">
                                                <h5>Lesson Learned</h5>
                                                <h4>10/50</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="col-lg-3 col-md-3 d-flex align-items-center justify-content-center"
                                >
                                    <div class="skip-group">
                                        <button
                                            type="submit"
                                            class="btn btn-info skip-btn"
                                        >
                                            skip
                                        </button>
                                        <button
                                            type="submit"
                                            class="btn btn-info continue-btn"
                                        >
                                            Continue
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-lg-12 col-xl-12 d-flex">
                            <div class="card flex-fill comman-shadow">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <h5 class="card-title">
                                                Learning Activity
                                            </h5>
                                        </div>
                                        <div class="col-6">
                                            <ul class="chart-list-out">
                                                <li>
                                                    <span
                                                        class="circle-blue"
                                                    ></span
                                                    >Teacher
                                                </li>
                                                <li>
                                                    <span
                                                        class="circle-green"
                                                    ></span
                                                    >Student
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
                    </div>
                    <!-- 
                             <div class="row">
                        <div class="col-12 col-lg-12 col-xl-4 d-flex">
                            <div class="card flex-fill comman-shadow">
                                <div class="card-body">
                                    <div
                                        id="calendar-doctor"
                                        class="calendar-container"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    -->
                    <!-- calendar trial -->
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <div class="flex justify-between items-center mb-4">
                            <button
                                id="prev"
                                class="bg-blue-500 text-white px-4 py-2 rounded"
                            >
                                Prev
                            </button>
                            <h2 id="monthYear" class="text-xl font-bold"></h2>
                            <button
                                id="next"
                                class="bg-blue-500 text-white px-4 py-2 rounded"
                            >
                                Next
                            </button>
                        </div>
                        <div class="grid grid-cols-7 gap-4">
                            <div class="text-center font-bold">Sun</div>
                            <div class="text-center font-bold">Mon</div>
                            <div class="text-center font-bold">Tue</div>
                            <div class="text-center font-bold">Wed</div>
                            <div class="text-center font-bold">Thu</div>
                            <div class="text-center font-bold">Fri</div>
                            <div class="text-center font-bold">Sat</div>
                        </div>
                        <div
                            id="days"
                            class="grid grid-cols-7 gap-4 mt-2"
                        ></div>
                    </div>
                    <!-- calendar trial end -->
                </div>
            </div>

            <p>
                melihat tugas, mengumpulkan tugas, melihat presensi, melakukan
                presensi, melihat jadwal pelajaran
            </p>
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
                <form action="#" method="GET" class="md:hidden mb-2">
                    <label for="sidebar-search" class="sr-only">Search</label>
                    <div class="relative">
                        <div
                            class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none"
                        >
                            <svg
                                class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                ></path>
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="search"
                            id="sidebar-search"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Search"
                        />
                    </div>
                </form>
                <ul class="space-y-2">
                    <li>
                        <a
                            href="teachersDashboard"
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
                        <a
                            href="teachers"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
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
                            <span class="ml-3">Data Siswa</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="#"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
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
                            <span class="ml-3"> Tugas Siswa</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="bukuPenghubung"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                        >
                            <svg
                                viewBox="0 0 576 512"
                                class="w-6 h-6"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M144.3 32.04C106.9 31.29 63.7 41.44 18.6 61.29c-11.42 5.026-18.6 16.67-18.6 29.15l0 357.6c0 11.55 11.99 19.55 22.45 14.65c126.3-59.14 219.8 11 223.8 14.01C249.1 478.9 252.5 480 256 480c12.4 0 16-11.38 16-15.98V80.04c0-5.203-2.531-10.08-6.781-13.08C263.3 65.58 216.7 33.35 144.3 32.04zM557.4 61.29c-45.11-19.79-88.48-29.61-125.7-29.26c-72.44 1.312-118.1 33.55-120.9 34.92C306.5 69.96 304 74.83 304 80.04v383.1C304 468.4 307.5 480 320 480c3.484 0 6.938-1.125 9.781-3.328c3.925-3.018 97.44-73.16 223.8-14c10.46 4.896 22.45-3.105 22.45-14.65l.0001-357.6C575.1 77.97 568.8 66.31 557.4 61.29z"
                                />
                            </svg>
                            <span class="ml-3">Buku Penghubung</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</template>

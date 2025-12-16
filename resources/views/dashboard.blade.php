<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite('resources/css/app.css')
  <title>Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
  </style>
</head>

<body class="poppins-regular text-gray-800 bg-gray-100">
  <header class="flex px-8 py-6 justify-between shadow-sm text-[14px] bg-white">
    <div class="flex gap-8">
      <a href="" class="font-medium border-b-2">Dashboard</a>
      <a href="/purchasing" class="">Purchasing</a>
      <a href="" class="">Sales</a>
      <a href="/mondriansales" class="">Mondrian Sales</a>
      <a href="/mondrianpurchasing" class="">Mondrian Purchasing</a>
    </div>
    <div class="flex items-center gap-4">
  <div class="flex flex-col leading-tight text-right">
    <span class="text-[12px] text-gray-500">Hai,</span>
    <span class="font-semibold text-[14px]">
      {{ session('schuser_name') }}
    </span>
  </div>

  <form action="{{ route('logout') }}" method="POST">
    @csrf
    <button
      type="submit"
      class="px-4 py-1.5 rounded-md bg-red-50 text-red-600 font-medium hover:bg-red-100 transition">
      Logout
    </button>
  </form>
</div>

  </header>
  <section class="px-8 pt-8 text-[14px] h-full">
    <div class="grid grid-cols-3 grid-rows-3 w-full gap-5 ">
      <div class="flex flex-col w-full p-6 rounded-xl shadow-sm bg-white border-0.5 row-span-3">
        <div class="relative flex flex-row gap-[14px] items-center">
          <img class="rounded-md bg-gray-200 p-1.5 w-[32px]" src="img/uang.svg" alt="">
          <p class="text-[18px] font-medium opacity-90">Total Laba / Rugi</p>
        </div>
        <p class="text-[32px] mt-2 font-bold {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
          {{ $totalProfit >= 0 ? '+' : '-' }} ${{ number_format(abs($totalProfit)) }}
        </p>
        <p class="text-[14px] {{ $roi >= 0 ? 'text-green-600' : 'text-red-600' }}">
          <span class="text-gray-800">Pertumbuhan Laba Usaha :</span> {{ $roi >= 0 ? '▲' : '▼' }} {{ number_format(abs($roi), 2) }}%
        </p>
        <div class="flex flex-col border-t-2 border-gray-200 mt-4 pt-4 gap-2">
          <p>Bulan Terbaik : <span class="font-semibold">November ($1,100)</span></p>
          <p>Bulan Terburuk : <span class="font-semibold">Januari (- $1,100)</span></p>
        </div>
      </div>
      <div class="flex flex-col w-full p-6 rounded-xl shadow-sm bg-white border-0.5">
        <div class="flex gap-[14px] items-center">
          <img class="rounded-md bg-gray-200 p-1.5 w-[32px]" src="img/uangmasuk.svg" alt="">
          <p class="text-[18px] font-medium opacity-90">Total Pendapatan</p>
        </div>
        <p class="text-[32px] mt-2 font-bold {{ $totalSales >= 0 ? 'text-green-600' : 'text-red-600' }}">
          ${{ number_format(abs($totalSales)) }}
        </p>
        <p>Total Transaksi : <span class="font-semibold">{{ number_format($totalSalesTransaction) }}</span></p>
      </div>
      <div class="flex flex-col w-full p-6 rounded-xl shadow-sm bg-white border-0.5">
        <div class="flex gap-[14px] items-center">
          <img class="rounded-md bg-gray-200 p-1.5 w-[32px]" src="img/uangkeluar.svg" alt="">
          <p class="text-[18px] font-medium opacity-90">Total Pengeluaran</p>
        </div>
        <p class="text-[32px] mt-2 font-bold {{ $totalPurchasing >= 0 ? 'text-red-600' : 'text-green-600' }}">
          ${{ number_format(abs($totalPurchasing)) }}
        </p>
        <p>Total Transaksi : <span class="font-semibold">{{ number_format($totalPurchasingTransaction) }}</span></p>
      </div>
      <div class="flex flex-col w-full p-6 rounded-xl shadow-sm bg-white border-0.5 gap-2 row-span-2 col-span-2">
        <div class="flex justify-between items-center">
          <div class="flex gap-[14px] items-center">
            <img class="rounded-md bg-gray-200 p-1.5 w-[32px]" src="img/grafik.svg" alt="">
            <p class="text-[18px] font-medium opacity-90">Grafik</p>
          </div>
          <div class="flex text-[12px]">
            <button id="btnMonth" class="px-4 py-1.5 bg-[#1b2691] text-white rounded-l-md border-y border-l border-gray-200 cursor-pointer">3 Tahun (B)</button>
            <button id="btnYear" class="px-4 py-1.5 bg-white rounded-r-md border-y border-r border-gray-200 cursor-pointer">Semua (T)</button>
          </div>
        </div>
        <div id="chartMonthContainer">
          <canvas id="profitChart" height="70"></canvas>
        </div>

        <div id="chartYearContainer" class="hidden">
          <canvas id="profitYearChart" height="70"></canvas>
        </div>
      </div>
    </div>
  </section>
</body>
<script>
  window.addEventListener('DOMContentLoaded', function() {
    // DATA BULANAN
    const monthlyLabels = {!!json_encode(array_column($profitPerMonth, 'bulan_tahun')) !!};
    const monthlyProfit = {!!json_encode(array_column($profitPerMonth, 'profit')) !!};
    const monthlySales = {!!json_encode(array_column($profitPerMonth, 'total_sales')) !!};
    const monthlyPurchasing = {!!json_encode(array_column($profitPerMonth, 'total_purchasing')) !!};

    // DATA TAHUNAN
    const yearLabels = {!!json_encode($profitPerYear -> pluck('tahun')) !!};
    const yearProfit = {!!json_encode($profitPerYear -> pluck('profit')) !!};
    const yearSales = {!!json_encode($profitPerYear -> pluck('total_sales')) !!};
    const yearPurchasing = {!!json_encode($profitPerYear -> pluck('total_purchasing')) !!};

    function hexToRgba(hex, opacity) {
      let r = parseInt(hex.slice(1, 3), 16);
      let g = parseInt(hex.slice(3, 5), 16);
      let b = parseInt(hex.slice(5, 7), 16);
      return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    }

    const borderProfit = "#1b2691";
    const borderSales = "#16A34A";
    const borderPurchasing = "#DC2626";
    const fillProfit = hexToRgba(borderProfit, 0.2);
    const fillSales = hexToRgba(borderSales, 0.2);
    const fillPurchasing = hexToRgba(borderPurchasing, 0.2);

    // Chart Bulanan
    const ctxMonth = document.getElementById("profitChart").getContext("2d");
    const monthChart = new Chart(ctxMonth, {
      type: 'line',
      data: {
        labels: monthlyLabels,
        datasets: [{
            label: "Laba / Rugi",
            data: monthlyProfit,
            borderColor: borderProfit,
            backgroundColor: fillProfit,
            fill: true,
            tension: 0.4,
            borderWidth: 1.5,
            pointRadius: 1
          },
          {
            label: "Pendapatan",
            data: monthlySales,
            borderColor: borderSales,
            backgroundColor: fillSales,
            fill: true,
            tension: 0.4,
            borderWidth: 1.5,
            pointRadius: 1
          },
          {
            label: "Pengeluaran",
            data: monthlyPurchasing,
            borderColor: borderPurchasing,
            backgroundColor: fillPurchasing,
            fill: true,
            tension: 0.4,
            borderWidth: 1.5,
            pointRadius: 1
          }
        ]
      }
    });

    // Chart Tahunan
    const ctxYear = document.getElementById("profitYearChart").getContext("2d");
    const yearChart = new Chart(ctxYear, {
      type: 'line',
      data: {
        labels: yearLabels,
        datasets: [{
            label: "Laba / Rugi",
            data: yearProfit,
            borderColor: borderProfit,
            backgroundColor: fillProfit,
            fill: true,
            tension: 0.3,
            borderWidth: 1.5,
            pointRadius: 1
          },
          {
            label: "Pendapatan",
            data: yearSales,
            borderColor: borderSales,
            backgroundColor: fillSales,
            fill: true,
            tension: 0.3,
            borderWidth: 1.5,
            pointRadius: 1
          },
          {
            label: "Pengeluaran",
            data: yearPurchasing,
            borderColor: borderPurchasing,
            backgroundColor: fillPurchasing,
            fill: true,
            tension: 0.3,
            borderWidth: 1.5,
            pointRadius: 1
          }
        ]
      }
    });

    // Tombol switch
    const btnMonth = document.getElementById("btnMonth");
    const btnYear = document.getElementById("btnYear");
    const chartMonthContainer = document.getElementById("chartMonthContainer");
    const chartYearContainer = document.getElementById("chartYearContainer");

    btnMonth.addEventListener("click", () => {
      chartMonthContainer.classList.remove("hidden");
      chartYearContainer.classList.add("hidden");
      btnMonth.classList.add("bg-[#1b2691]", "text-white");
      btnMonth.classList.remove("bg-white", "text-gray-800");
      btnYear.classList.add("bg-white", "text-gray-800");
      btnYear.classList.remove("bg-[#1b2691]", "text-white");
    });

    btnYear.addEventListener("click", () => {
      chartYearContainer.classList.remove("hidden");
      chartMonthContainer.classList.add("hidden");
      btnYear.classList.add("bg-[#1b2691]", "text-white");
      btnYear.classList.remove("bg-white", "text-gray-800");
      btnMonth.classList.add("bg-white", "text-gray-800");
      btnMonth.classList.remove("bg-[#1b2691]", "text-white");
    });
  });
</script>

</html>

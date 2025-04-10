<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Card from '@/Components/ui/card/Card.vue';
import CardHeader from '@/Components/ui/card/CardHeader.vue';
import CardTitle from '@/Components/ui/card/CardTitle.vue';
import CardContent from '@/Components/ui/card/CardContent.vue';
import CardFooter from '@/Components/ui/card/CardFooter.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Icon from '@/Components/Icon.vue';
import VueApexCharts from 'vue3-apexcharts';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

// Definir propriedades
const props = defineProps<{
  stats?: {
    pending: { count: number; total: number };
    overdue: { count: number; total: number };
    nextWeek: { count: number; total: number };
    paid: { count: number; total: number };
  };
  invoicesByStatus?: Array<{ status: string; count: number }>;
  dueCalendar?: Array<{ date: string; count: number; total: number }>;
  topSuppliers?: Array<{ name: string; count: number; total: number }>;
  monthlyData?: Array<{ month: string; paid: number; pending: number; overdue: number }>;
  alertsToday?: Array<{ id: number; invoice_number: string; supplier: string; total: number }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

// Formatação de moeda
const formatCurrency = (value: number): string => {
  // Formata com locale padrão, mas depois substitui para colocar o símbolo € no início
  const formatted = new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(value || 0);
  
  // Remove o símbolo € e espaço que está no final (ex: "1.234,56 €")
  const valueWithoutSymbol = formatted.replace(/\s?€$/, '');
  
  // Retorna com o símbolo € no início
  return `€ ${valueWithoutSymbol}`;
};

// Configuração dos gráficos
const statusChartOptions = ref({
  chart: {
    type: 'donut',
    height: 300,
    foreColor: '#94a3b8',
  },
  labels: ['Pago', 'Pendente', 'Atrasado', 'Cancelado'],
  colors: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
  legend: {
    position: 'bottom',
    fontSize: '14px',
    fontWeight: 600,
    labels: {
      colors: '#94a3b8',
    },
    markers: {
      fillColors: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
    },
  },
  dataLabels: {
    enabled: true,
    style: {
      fontSize: '14px',
      fontWeight: 'bold',
      colors: ['#000000'],
    },
    background: {
      enabled: true,
      borderRadius: 2,
      foreColor: '#ffffff',
      opacity: 0.9,
      padding: 4,
    },
  },
  plotOptions: {
    pie: {
      donut: {
        size: '60%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            formatter: function(w) {
              return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toString();
            },
            color: '#94a3b8',
          },
        },
      },
    },
  },
});

const monthlyChartOptions = ref({
  chart: {
    type: 'area',
    height: 350,
    toolbar: {
      show: false,
    },
    foreColor: '#94a3b8',
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    curve: 'smooth',
    width: 3,
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.7,
      opacityTo: 0.3,
    },
  },
  colors: ['#10b981', '#f59e0b', '#ef4444'],
  xaxis: {
    categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
    labels: {
      style: {
        colors: '#94a3b8',
        fontWeight: 600,
      },
    },
  },
  yaxis: {
    labels: {
      formatter: function (value: number) {
        return formatCurrency(value);
      },
      style: {
        colors: ['#94a3b8'],
        fontWeight: 600,
      },
    },
  },
  tooltip: {
    theme: 'dark',
    x: {
      show: true,
      formatter: (val) => `Mês: ${val}`,
    },
    y: {
      formatter: (val) => formatCurrency(val),
    },
    marker: {
      show: true,
    },
    style: {
      fontSize: '12px',
    },
    fixed: {
      enabled: false,
    },
  },
  grid: {
    borderColor: '#334155',
    strokeDashArray: 5,
  },
  legend: {
    position: 'bottom',
    fontSize: '14px',
    fontWeight: 600,
    labels: {
      colors: '#94a3b8',
    },
    markers: {
      fillColors: ['#10b981', '#f59e0b', '#ef4444'],
    },
  },
});

const statusSeries = ref([10, 40, 30, 20]);
const monthlySeries = ref([
  {
    name: 'Pagos',
    data: [1000, 1500, 2000, 1800, 2200, 3000],
  },
  {
    name: 'Pendentes',
    data: [500, 700, 600, 800, 900, 1000],
  },
  {
    name: 'Atrasados',
    data: [200, 300, 400, 100, 200, 300],
  },
]);

// Inicializar os gráficos quando o componente for montado
onMounted(() => {
  if (props.invoicesByStatus && props.invoicesByStatus.length > 0) {
    statusChartOptions.value.labels = props.invoicesByStatus.map(item => item.status);
    statusSeries.value = props.invoicesByStatus.map(item => item.count);
  }

  if (props.monthlyData && props.monthlyData.length > 0) {
    monthlyChartOptions.value.xaxis.categories = props.monthlyData.map(item => item.month);
    monthlySeries.value = [
      {
        name: 'Pagos',
        data: props.monthlyData.map(item => item.paid),
      },
      {
        name: 'Pendentes',
        data: props.monthlyData.map(item => item.pending),
      },
      {
        name: 'Atrasados',
        data: props.monthlyData.map(item => item.overdue),
      },
    ];
  }
});

// Função para exportar dados para CSV
const downloadCSV = () => {
  // Cabeçalho do CSV
  let csvContent = "data:text/csv;charset=utf-8,";
  
  // Adiciona informações de resumo
  csvContent += "Tipo,Quantidade,Valor Total\r\n";
  csvContent += `Pendentes,${props.stats?.pending?.count || 0},${props.stats?.pending?.total || 0}\r\n`;
  csvContent += `Vencidas,${props.stats?.overdue?.count || 0},${props.stats?.overdue?.total || 0}\r\n`;
  csvContent += `Próximos 7 dias,${props.stats?.nextWeek?.count || 0},${props.stats?.nextWeek?.total || 0}\r\n`;
  csvContent += `Pagas,${props.stats?.paid?.count || 0},${props.stats?.paid?.total || 0}\r\n\r\n`;
  
  // Adiciona dados de status
  if (props.invoicesByStatus && props.invoicesByStatus.length > 0) {
    csvContent += "Status,Quantidade\r\n";
    props.invoicesByStatus.forEach(item => {
      csvContent += `${item.status},${item.count}\r\n`;
    });
    csvContent += "\r\n";
  }
  
  // Adiciona dados mensais
  if (props.monthlyData && props.monthlyData.length > 0) {
    csvContent += "Mês,Pagas,Pendentes,Vencidas\r\n";
    props.monthlyData.forEach(item => {
      csvContent += `${item.month},${item.paid},${item.pending},${item.overdue}\r\n`;
    });
    csvContent += "\r\n";
  }
  
  // Adiciona dados de fornecedores
  if (props.topSuppliers && props.topSuppliers.length > 0) {
    csvContent += "Fornecedor,Quantidade,Valor Total\r\n";
    props.topSuppliers.forEach(supplier => {
      csvContent += `${supplier.name},${supplier.count},${supplier.total}\r\n`;
    });
    csvContent += "\r\n";
  }
  
  // Adiciona alertas do dia
  if (props.alertsToday && props.alertsToday.length > 0) {
    csvContent += "Fatura,Fornecedor,Valor Total\r\n";
    props.alertsToday.forEach(alert => {
      csvContent += `${alert.invoice_number},${alert.supplier},${alert.total}\r\n`;
    });
  }
  
  // Cria elemento para download
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `dashboard_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Botão de download CSV -->
      <div class="flex justify-end">
        <Button @click="downloadCSV" class="flex items-center gap-2">
          <Icon name="lucide:download" class="h-4 w-4" />
          Exportar CSV
        </Button>
      </div>
      
      <!-- Cards de resumo -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <!-- Pendentes -->
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">Faturas Pendentes</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex items-center">
              <div class="mr-2 rounded-full bg-amber-100 p-2 dark:bg-amber-900">
                <Icon name="lucide:alert-circle" class="h-4 w-4 text-amber-600 dark:text-amber-400" />
              </div>
              <div>
                <div class="text-2xl font-bold">{{ stats?.pending?.count || 0 }}</div>
                <div class="text-xs text-muted-foreground">{{ formatCurrency(stats?.pending?.total || 0) }}</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Vencidas -->
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">Faturas Vencidas</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex items-center">
              <div class="mr-2 rounded-full bg-red-100 p-2 dark:bg-red-900">
                <Icon name="lucide:alert-triangle" class="h-4 w-4 text-red-600 dark:text-red-400" />
              </div>
              <div>
                <div class="text-2xl font-bold">{{ stats?.overdue?.count || 0 }}</div>
                <div class="text-xs text-muted-foreground">{{ formatCurrency(stats?.overdue?.total || 0) }}</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Próximos 7 dias -->
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">Próximos 7 dias</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex items-center">
              <div class="mr-2 rounded-full bg-blue-100 p-2 dark:bg-blue-900">
                <Icon name="lucide:calendar-days" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <div class="text-2xl font-bold">{{ stats?.nextWeek?.count || 0 }}</div>
                <div class="text-xs text-muted-foreground">{{ formatCurrency(stats?.nextWeek?.total || 0) }}</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Pagas -->
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">Faturas Pagas</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex items-center">
              <div class="mr-2 rounded-full bg-green-100 p-2 dark:bg-green-900">
                <Icon name="lucide:check-circle" class="h-4 w-4 text-green-600 dark:text-green-400" />
              </div>
              <div>
                <div class="text-2xl font-bold">{{ stats?.paid?.count || 0 }}</div>
                <div class="text-xs text-muted-foreground">{{ formatCurrency(stats?.paid?.total || 0) }}</div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Gráficos e widgets -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <!-- Gráfico de Status -->
        <Card class="col-span-1">
          <CardHeader>
            <CardTitle>Status das Faturas</CardTitle>
          </CardHeader>
          <CardContent>
            <VueApexCharts type="donut" height="300" :options="statusChartOptions" :series="statusSeries" />
          </CardContent>
        </Card>

        <!-- Gráfico de Valores Mensais -->
        <Card class="col-span-1 lg:col-span-2">
          <CardHeader>
            <CardTitle>Valores por Mês</CardTitle>
          </CardHeader>
          <CardContent>
            <VueApexCharts type="area" height="350" :options="monthlyChartOptions" :series="monthlySeries" />
          </CardContent>
        </Card>

        <!-- Top Fornecedores -->
        <Card class="col-span-1">
          <CardHeader>
            <CardTitle>Top Fornecedores</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div v-for="(supplier, index) in topSuppliers || []" :key="index" class="flex items-center justify-between">
                <div class="truncate">
                  <div class="truncate font-medium">{{ supplier.name }}</div>
                  <div class="text-xs text-muted-foreground">{{ supplier.count }} faturas</div>
                </div>
                <div class="font-semibold">{{ formatCurrency(supplier.total) }}</div>
              </div>
              <div v-if="!topSuppliers || topSuppliers.length === 0" class="text-center text-sm text-muted-foreground">
                Nenhum fornecedor encontrado
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Alertas para Hoje -->
        <Card class="col-span-1 lg:col-span-2">
          <CardHeader>
            <CardTitle>Vencimentos Hoje</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div v-for="(alert, index) in alertsToday || []" :key="index" class="flex items-center justify-between">
                <div>
                  <Link :href="route('invoices.show', alert.id)" class="hover:underline">
                    <div class="font-medium">{{ alert.invoice_number }}</div>
                    <div class="text-xs text-muted-foreground">{{ alert.supplier }}</div>
                  </Link>
                </div>
                <div class="font-semibold">{{ formatCurrency(alert.total) }}</div>
                <Badge variant="destructive">Vence Hoje</Badge>
              </div>
              <div v-if="!alertsToday || alertsToday.length === 0" class="text-center text-sm text-muted-foreground">
                Nenhum vencimento para hoje
              </div>
            </div>
          </CardContent>
          <CardFooter v-if="alertsToday && alertsToday.length > 0">
            <Link :href="route('invoices.index')" class="text-sm text-blue-600 hover:underline dark:text-blue-400">
              Ver todas as faturas →
            </Link>
          </CardFooter>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

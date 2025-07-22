<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Set chart defaults for better UI
    Chart.defaults.font.size = 11;
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;
    
    // Gender Distribution Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    const genderChart = new Chart(genderCtx, {
      type: 'pie',
      data: {
        labels: ['Hommes', 'Femmes'],
        datasets: [{
          data: [<?php echo $maleCount; ?>, <?php echo $femaleCount; ?>],
          backgroundColor: ['#0d6efd', '#dc3545'],
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            bodyFont: {
              size: 11
            },
            titleFont: {
              size: 12
            }
          }
        }
      }
    });
    
    // Appointment Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: ['Planifiés', 'Terminés', 'Annulés', 'Absences'],
        datasets: [{
          data: [<?php echo $scheduled; ?>, <?php echo $completed; ?>, <?php echo $cancelled; ?>, <?php echo $noShow; ?>],
          backgroundColor: ['#0d6efd', '#198754', '#dc3545', '#ffc107'],
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            bodyFont: {
              size: 11
            },
            titleFont: {
              size: 12
            }
          }
        }
      }
    });
    
    // Age Distribution Chart
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    const ageChart = new Chart(ageCtx, {
      type: 'bar',
      data: {
        labels: ['< 18 ans', '18-40 ans', '41-60 ans', '> 60 ans'],
        datasets: [{
          label: 'Patients',
          data: [
            <?php echo $ageDistribution['under_18']; ?>, 
            <?php echo $ageDistribution['age_18_40']; ?>, 
            <?php echo $ageDistribution['age_41_60']; ?>, 
            <?php echo $ageDistribution['over_60']; ?>
          ],
          backgroundColor: [
            'rgba(23, 162, 184, 0.7)',
            'rgba(13, 110, 253, 0.7)',
            'rgba(25, 135, 84, 0.7)',
            'rgba(255, 193, 7, 0.7)'
          ],
          borderColor: [
            'rgba(23, 162, 184, 1)',
            'rgba(13, 110, 253, 1)',
            'rgba(25, 135, 84, 1)',
            'rgba(255, 193, 7, 1)'
          ],
          borderWidth: 1,
          barPercentage: 0.6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
              font: {
                size: 10
              }
            },
            grid: {
              display: true,
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            ticks: {
              font: {
                size: 10
              }
            },
            grid: {
              display: false
            }
          }
        },
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            bodyFont: {
              size: 11
            },
            titleFont: {
              size: 12
            }
          }
        }
      }
    });
  });
</script>

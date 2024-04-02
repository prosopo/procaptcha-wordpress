/* global Chart, ProcaptchaEventsObject */

/**
 * @param ProcaptchaEventsObject.succeed
 * @param ProcaptchaEventsObject.failed
 * @param ProcaptchaEventsObject.unit
 * @param ProcaptchaEventsObject.succeedLabel
 * @param ProcaptchaEventsObject.failedLabel
 */
document.addEventListener( 'DOMContentLoaded', function() {
	const ctx = document.getElementById( 'eventsChart' );

	new Chart( ctx, {
		type: 'bar',
		data: {
			datasets: [
				{
					label: ProcaptchaEventsObject.succeedLabel,
					data: ProcaptchaEventsObject.succeed,
					barThickness: 'flex',
					borderWidth: 1,
				},
				{
					label: ProcaptchaEventsObject.failedLabel,
					data: ProcaptchaEventsObject.failed,
					barThickness: 'flex',
					borderWidth: 1,
				},
			],
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			aspectRatio: 3,
			scales: {
				x: {
					type: 'time',
					time: {
						displayFormats: {
							millisecond: 'HH:mm:ss',
							second: 'HH:mm:ss',
							minute: 'HH:mm',
							hour: 'HH:mm',
							day: 'dd.MM.yyyy',
							week: 'dd.MM.yyyy',
							month: 'dd.MM.yyyy',
							quarter: 'dd.MM.yyyy',
							year: 'dd.MM.yyyy',
						},
						tooltipFormat: 'dd.MM.yyyy HH:mm',
						unit: ProcaptchaEventsObject.unit,
					},
				},
				y: {
					beginAtZero: true,
					ticks: {
						precision: 0,
					},
				},
			},
		},
	} );
} );

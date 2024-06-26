let pyodideLoadingPromise = loadPyodide({ indexURL: "https://cdn.jsdelivr.net/pyodide/v0.18.1/full/" });

function showLoadingIndicator() {
    document.getElementById('loading-indicator').style.display = 'block';
}

function hideLoadingIndicator() {
    document.getElementById('loading-indicator').style.display = 'none';
}

async function processCSVWithPyodide(csvContent) {
    showLoadingIndicator();
    const pyodide = await pyodideLoadingPromise;
    await pyodide.loadPackage(['pandas']);
    const pythonCode = `
import pandas as pd
from io import StringIO

def process_data(csv_content):
    df = pd.read_csv(StringIO(csv_content))
    df['Date'] = pd.to_datetime(df['Date'], errors='coerce')
    df.set_index('Date', inplace=True)
    monthly_profits = df.resample('M').sum()
    return monthly_profits.to_json(orient='split')

csv_content = """${csvContent.replace(/\\/g, '\\\\').replace(/`/g, '\\`')}"""
processed_data = process_data(csv_content)
processed_data
    `;
    try {
        let result = await pyodide.runPythonAsync(pythonCode);
        let resultObject = JSON.parse(result);
        clearChartData();
        prepareChartData(resultObject);
        drawChart();
    } catch (error) {
        console.error('Failed to process CSV with Pyodide:', error);
    } finally {
        hideLoadingIndicator();
    }
}

function clearChartData() {
    chartData.length = 1;
}

function prepareChartData(resultObject) {
    resultObject.index.forEach((monthString, i) => {
        const date = new Date(monthString);
        const monthName = date.toLocaleString('default', { month: 'long' });
        const profit = resultObject.data[i][0];
        chartData.push([monthName, profit]);
    });
}

let chartData = [['Month', 'Profit']];
let chart;

function drawChart() {
    const data = google.visualization.arrayToDataTable(chartData);
    const options = {
        title: 'Monthly Profits',
        curveType: 'function',
        legend: { position: 'bottom' },
        hAxis: { title: 'Month' },
        vAxis: { title: 'Profit' },
        animation: {
            duration: 1000,
            easing: 'out',
            startup: true
        },
        height: 500
    };
    chart = new google.visualization.LineChart(document.getElementById('chart-container'));
    chart.draw(data, options);
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('data-upload-form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('datafile');
        const uploadButton = document.getElementById('upload-btn');
        const file = fileInput.files[0];
        if (file) {
            uploadButton.disabled = true;
            const reader = new FileReader();
            reader.onload = function(e) {
                processCSVWithPyodide(e.target.result).then(() => {
                    uploadButton.disabled = false;
                });
            };
            reader.readAsText(file);
        } else {
            alert('Please select a file to upload.');
        }
    });
});

google.charts.load('current', { packages: ['corechart'] });


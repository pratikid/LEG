/**
 * LEG Analytics Dashboard - Interactive D3.js Visualizations
 * Provides comprehensive analytics and insights for genealogical data
 */

import * as d3 from 'd3';

class AnalyticsDashboard {
    constructor(containerId) {
        this.container = d3.select(`#${containerId}`);
        this.data = {};
        this.charts = {};
        this.colors = {
            primary: '#3B82F6',
            secondary: '#8B5CF6', 
            success: '#10B981',
            warning: '#F59E0B',
            danger: '#EF4444',
            info: '#06B6D4',
            male: '#3B82F6',
            female: '#EC4899',
            unknown: '#6B7280'
        };
        this.init();
    }

    init() {
        this.setupLayout();
        this.bindEvents();
        this.loadData();
    }

    setupLayout() {
        // Create main dashboard layout
        const dashboard = this.container
            .append('div')
            .attr('class', 'analytics-dashboard grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 p-6');

        // Summary Cards Section
        this.createSummaryCards();
        
        // Chart Sections
        this.createChartContainers();
    }

    createSummaryCards() {
        const summarySection = this.container
            .append('div')
            .attr('class', 'summary-cards grid grid-cols-1 md:grid-cols-4 gap-4 mb-8');

        const cardTypes = [
            { id: 'total-individuals', title: 'Total Individuals', icon: '👥' },
            { id: 'total-families', title: 'Family Units', icon: '🏠' },
            { id: 'total-generations', title: 'Generations', icon: '🌳' },
            { id: 'completion-rate', title: 'Data Completeness', icon: '📊' }
        ];

        cardTypes.forEach(card => {
            summarySection
                .append('div')
                .attr('class', 'summary-card bg-white p-6 rounded-lg shadow-md')
                .attr('id', card.id)
                .html(`
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">${card.title}</p>
                            <p class="text-2xl font-bold text-gray-900 metric-value">-</p>
                        </div>
                        <div class="text-3xl">${card.icon}</div>
                    </div>
                    <div class="mt-4">
                        <div class="text-sm text-gray-500 trend-indicator"></div>
                    </div>
                `);
        });
    }

    createChartContainers() {
        const chartConfigs = [
            { id: 'birth-year-distribution', title: 'Birth Year Distribution', type: 'histogram' },
            { id: 'gender-distribution', title: 'Gender Distribution', type: 'pie' },
            { id: 'surname-frequency', title: 'Top Surnames', type: 'bar' },
            { id: 'generation-timeline', title: 'Generation Timeline', type: 'timeline' },
            { id: 'relationship-network', title: 'Family Network', type: 'network' },
            { id: 'lifespan-analysis', title: 'Lifespan Analysis', type: 'scatter' },
            { id: 'geographic-distribution', title: 'Geographic Distribution', type: 'map' },
            { id: 'import-performance', title: 'Import Performance', type: 'line' }
        ];

        chartConfigs.forEach(chart => {
            const chartContainer = this.container
                .append('div')
                .attr('class', 'chart-container bg-white p-6 rounded-lg shadow-md')
                .attr('id', `${chart.id}-container`);

            chartContainer
                .append('h3')
                .attr('class', 'text-lg font-semibold text-gray-900 mb-4')
                .text(chart.title);

            chartContainer
                .append('div')
                .attr('class', 'chart-content')
                .attr('id', chart.id);
        });
    }

    async loadData() {
        try {
            // Load analytics data from API
            const [treeStats, demographics, performance] = await Promise.all([
                fetch('/api/v1/analytics/dashboard').then(r => r.json()),
                fetch('/api/v1/analytics/demographics').then(r => r.json()),
                fetch('/api/v1/analytics/import-metrics').then(r => r.json())
            ]);

            this.data = {
                treeStats,
                demographics,
                performance
            };

            this.renderDashboard();
        } catch (error) {
            console.error('Failed to load analytics data:', error);
            this.showErrorState();
        }
    }

    renderDashboard() {
        this.updateSummaryCards();
        this.renderBirthYearDistribution();
        this.renderGenderDistribution();
        this.renderSurnameFrequency();
        this.renderGenerationTimeline();
        this.renderRelationshipNetwork();
        this.renderLifespanAnalysis();
        this.renderGeographicDistribution();
        this.renderImportPerformance();
    }

    updateSummaryCards() {
        const stats = this.data.treeStats;
        
        d3.select('#total-individuals .metric-value')
            .text(this.formatNumber(stats.total_individuals || 0));
        
        d3.select('#total-families .metric-value')
            .text(this.formatNumber(stats.total_families || 0));
        
        d3.select('#total-generations .metric-value')
            .text(stats.max_generations || 0);
        
        d3.select('#completion-rate .metric-value')
            .text(`${stats.completion_percentage || 0}%`);
    }

    renderBirthYearDistribution() {
        const container = d3.select('#birth-year-distribution');
        const data = this.data.demographics.birth_year_distribution || [];
        
        const margin = { top: 20, right: 30, bottom: 40, left: 50 };
        const width = 400 - margin.left - margin.right;
        const height = 300 - margin.top - margin.bottom;

        const svg = container.append('svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom);

        const g = svg.append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

        const x = d3.scaleLinear()
            .domain(d3.extent(data, d => d.year))
            .range([0, width]);

        const y = d3.scaleLinear()
            .domain([0, d3.max(data, d => d.count)])
            .range([height, 0]);

        // Create histogram bars
        const histogram = d3.histogram()
            .value(d => d.year)
            .domain(x.domain())
            .thresholds(x.ticks(20));

        const bins = histogram(data);

        g.selectAll('.bar')
            .data(bins)
            .enter().append('rect')
            .attr('class', 'bar')
            .attr('x', d => x(d.x0))
            .attr('width', d => x(d.x1) - x(d.x0) - 1)
            .attr('y', d => y(d.length))
            .attr('height', d => height - y(d.length))
            .attr('fill', this.colors.primary)
            .on('mouseover', this.showTooltip)
            .on('mouseout', this.hideTooltip);

        // Add axes
        g.append('g')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x).tickFormat(d3.format('d')));

        g.append('g')
            .call(d3.axisLeft(y));
    }

    renderGenderDistribution() {
        const container = d3.select('#gender-distribution');
        const data = this.data.demographics.gender_distribution || [];
        
        const width = 300;
        const height = 300;
        const radius = Math.min(width, height) / 2;

        const svg = container.append('svg')
            .attr('width', width)
            .attr('height', height);

        const g = svg.append('g')
            .attr('transform', `translate(${width / 2},${height / 2})`);

        const color = d3.scaleOrdinal()
            .domain(data.map(d => d.gender))
            .range([this.colors.male, this.colors.female, this.colors.unknown]);

        const pie = d3.pie()
            .value(d => d.count)
            .sort(null);

        const path = d3.arc()
            .outerRadius(radius - 10)
            .innerRadius(60);

        const labelArc = d3.arc()
            .outerRadius(radius - 40)
            .innerRadius(radius - 40);

        const arcs = g.selectAll('.arc')
            .data(pie(data))
            .enter().append('g')
            .attr('class', 'arc');

        arcs.append('path')
            .attr('d', path)
            .attr('fill', d => color(d.data.gender))
            .on('mouseover', this.showTooltip)
            .on('mouseout', this.hideTooltip);

        arcs.append('text')
            .attr('transform', d => `translate(${labelArc.centroid(d)})`)
            .attr('dy', '.35em')
            .style('text-anchor', 'middle')
            .text(d => `${d.data.gender}: ${d.data.count}`);
    }

    renderSurnameFrequency() {
        const container = d3.select('#surname-frequency');
        const data = this.data.demographics.surname_frequency || [];
        
        const margin = { top: 20, right: 30, bottom: 60, left: 50 };
        const width = 400 - margin.left - margin.right;
        const height = 300 - margin.top - margin.bottom;

        const svg = container.append('svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom);

        const g = svg.append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

        const x = d3.scaleBand()
            .domain(data.map(d => d.surname))
            .range([0, width])
            .padding(0.1);

        const y = d3.scaleLinear()
            .domain([0, d3.max(data, d => d.count)])
            .range([height, 0]);

        g.selectAll('.bar')
            .data(data)
            .enter().append('rect')
            .attr('class', 'bar')
            .attr('x', d => x(d.surname))
            .attr('width', x.bandwidth())
            .attr('y', d => y(d.count))
            .attr('height', d => height - y(d.count))
            .attr('fill', this.colors.secondary)
            .on('mouseover', this.showTooltip)
            .on('mouseout', this.hideTooltip);

        // Add axes
        g.append('g')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x))
            .selectAll('text')
            .style('text-anchor', 'end')
            .attr('dx', '-.8em')
            .attr('dy', '.15em')
            .attr('transform', 'rotate(-45)');

        g.append('g')
            .call(d3.axisLeft(y));
    }

    renderRelationshipNetwork() {
        const container = d3.select('#relationship-network');
        const data = this.data.demographics.relationship_network || { nodes: [], links: [] };
        
        const width = 500;
        const height = 400;

        const svg = container.append('svg')
            .attr('width', width)
            .attr('height', height);

        const simulation = d3.forceSimulation(data.nodes)
            .force('link', d3.forceLink(data.links).id(d => d.id))
            .force('charge', d3.forceManyBody().strength(-300))
            .force('center', d3.forceCenter(width / 2, height / 2));

        const link = svg.append('g')
            .attr('class', 'links')
            .selectAll('line')
            .data(data.links)
            .enter().append('line')
            .attr('stroke-width', 2)
            .attr('stroke', '#999')
            .attr('stroke-opacity', 0.6);

        const node = svg.append('g')
            .attr('class', 'nodes')
            .selectAll('circle')
            .data(data.nodes)
            .enter().append('circle')
            .attr('r', d => Math.sqrt(d.connections) * 3 + 5)
            .attr('fill', d => d.gender === 'M' ? this.colors.male : 
                         d.gender === 'F' ? this.colors.female : this.colors.unknown)
            .call(d3.drag()
                .on('start', this.dragstarted)
                .on('drag', this.dragged)
                .on('end', this.dragended));

        node.append('title')
            .text(d => `${d.name}\nConnections: ${d.connections}`);

        simulation.on('tick', () => {
            link
                .attr('x1', d => d.source.x)
                .attr('y1', d => d.source.y)
                .attr('x2', d => d.target.x)
                .attr('y2', d => d.target.y);

            node
                .attr('cx', d => d.x)
                .attr('cy', d => d.y);
        });
    }

    renderImportPerformance() {
        const container = d3.select('#import-performance');
        const data = this.data.performance.import_history || [];
        
        const margin = { top: 20, right: 30, bottom: 40, left: 60 };
        const width = 500 - margin.left - margin.right;
        const height = 300 - margin.top - margin.bottom;

        const svg = container.append('svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom);

        const g = svg.append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

        const parseTime = d3.timeParse('%Y-%m-%d');
        data.forEach(d => {
            d.date = parseTime(d.date);
        });

        const x = d3.scaleTime()
            .domain(d3.extent(data, d => d.date))
            .range([0, width]);

        const y = d3.scaleLinear()
            .domain(d3.extent(data, d => d.duration))
            .range([height, 0]);

        const line = d3.line()
            .x(d => x(d.date))
            .y(d => y(d.duration))
            .curve(d3.curveMonotoneX);

        g.append('path')
            .datum(data)
            .attr('class', 'line')
            .attr('d', line)
            .attr('fill', 'none')
            .attr('stroke', this.colors.info)
            .attr('stroke-width', 2);

        // Add data points
        g.selectAll('.dot')
            .data(data)
            .enter().append('circle')
            .attr('class', 'dot')
            .attr('cx', d => x(d.date))
            .attr('cy', d => y(d.duration))
            .attr('r', 4)
            .attr('fill', this.colors.info)
            .on('mouseover', this.showTooltip)
            .on('mouseout', this.hideTooltip);

        // Add axes
        g.append('g')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x));

        g.append('g')
            .call(d3.axisLeft(y));
    }

    // Placeholder methods for remaining charts
    renderGenerationTimeline() {
        // Implementation for generation timeline visualization
    }

    renderLifespanAnalysis() {
        // Implementation for lifespan scatter plot
    }

    renderGeographicDistribution() {
        // Implementation for geographic map visualization
    }

    // Utility methods
    showTooltip(event, d) {
        const tooltip = d3.select('body')
            .append('div')
            .attr('class', 'tooltip')
            .style('opacity', 0)
            .style('position', 'absolute')
            .style('background', 'rgba(0, 0, 0, 0.8)')
            .style('color', 'white')
            .style('padding', '8px')
            .style('border-radius', '4px')
            .style('font-size', '12px');

        tooltip.transition()
            .duration(200)
            .style('opacity', .9);

        tooltip.html(this.formatTooltip(d))
            .style('left', (event.pageX + 10) + 'px')
            .style('top', (event.pageY - 28) + 'px');
    }

    hideTooltip() {
        d3.selectAll('.tooltip').remove();
    }

    formatTooltip(data) {
        if (typeof data === 'object' && data !== null) {
            return Object.entries(data)
                .map(([key, value]) => `${key}: ${value}`)
                .join('<br>');
        }
        return String(data);
    }

    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }

    // Drag handlers for network visualization
    dragstarted(event, d) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
    }

    dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }

    dragended(event, d) {
        if (!event.active) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }

    bindEvents() {
        // Add dashboard refresh functionality
        d3.select('#refresh-dashboard').on('click', () => {
            this.loadData();
        });

        // Add date range filtering
        d3.select('#date-filter').on('change', (event) => {
            this.filterByDateRange(event.target.value);
        });
    }

    filterByDateRange(range) {
        // Implementation for date range filtering
        console.log('Filtering by date range:', range);
    }

    showErrorState() {
        this.container.html(`
            <div class="error-state text-center p-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Unable to Load Analytics</h3>
                <p class="text-gray-600 mb-4">There was an error loading the analytics data.</p>
                <button id="retry-button" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Retry
                </button>
            </div>
        `);

        d3.select('#retry-button').on('click', () => {
            this.loadData();
        });
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('analytics-dashboard')) {
        new AnalyticsDashboard('analytics-dashboard');
    }
});

export default AnalyticsDashboard;

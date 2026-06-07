<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ad Report – <?= htmlspecialchars($ad->title ?? 'N/A') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #2c2c2c;
            background: #ffffff;
        }

        .page-header {
            background-color: #1a2332;
            padding: 18px 24px;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .page-header .logo {
            height: 48px;
            display: block;
        }

        .page-header .report-label {
            color: #a0aec0;
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 6px;
        }

        .info-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin: 0 24px 20px;
            overflow: hidden;
        }

        .info-card .card-title {
            background-color: #f7fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 14px;
            font-size: 10px;
            font-weight: bold;
            color: #4a5568;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 7px 14px;
            border-bottom: 1px solid #f0f4f8;
            vertical-align: top;
            line-height: 1.4;
        }

        .info-grid .label {
            width: 30%;
            color: #718096;
            font-size: 10px;
        }

        .info-grid .value {
            font-weight: bold;
            font-size: 11px;
            color: #1a202c;
        }

        .info-grid tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-active {
            background-color: #c6f6d5;
            color: #276749;
        }

        .badge-inactive {
            background-color: #e2e8f0;
            color: #4a5568;
        }

        .metrics-section {
            margin: 0 24px 24px;
        }

        .metrics-section .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4a5568;
            margin-bottom: 8px;
            border-bottom: 2px solid #1a2332;
            padding-bottom: 4px;
        }

        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .metrics-table thead tr {
            background-color: #1a2332;
            color: #ffffff;
        }

        .metrics-table thead th {
            padding: 9px 12px;
            text-align: left;
            font-size: 10px;
            letter-spacing: 0.3px;
        }

        .metrics-table thead th.text-right {
            text-align: right;
        }

        .metrics-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }

        .metrics-table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        .metrics-table tbody td {
            padding: 8px 12px;
            color: #2d3748;
            vertical-align: middle;
        }

        .metrics-table tbody td.metric-value {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
            font-weight: bold;
            color: #1a2332;
        }

        .metrics-table tbody td.metric-date {
            color: #718096;
            font-size: 10px;
        }

        .no-metrics {
            text-align: center;
            color: #a0aec0;
            font-style: italic;
            padding: 20px;
        }

        .page-footer {
            margin: 0 24px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: right;
            font-size: 9px;
            color: #a0aec0;
        }
    </style>
</head>
<body>

    <!-- header -->
    <div class="page-header">
        <div class="report-label">Ad Metrics Report</div>
    </div>

    <!-- card -->
    <div class="info-card">
        <div class="card-title">Ad Information</div>
        <table class="info-grid">
            <tr>
                <td class="label">Ad Title</td>
                <td class="value"><?= htmlspecialchars($ad->title ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Content ID</td>
                <td class="value" style="font-family: monospace; font-size:10px;">
                    <?= htmlspecialchars($ad->content_identifier ?? '-') ?>
                </td>
            </tr>
            <tr>
                <td class="label">Platform</td>
                <td class="value"><?= htmlspecialchars(ucfirst($ad->platform ?? '-')) ?></td>
            </tr>
            <tr>
                <td class="label">Client</td>
                <td class="value"><?= htmlspecialchars($ad->company_name ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">PIC</td>
                <td class="value"><?= htmlspecialchars($ad->pic_name ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="value">
                    <?php $active = (bool)($ad->is_active ?? false); ?>
                    <span class="badge <?= $active ? 'badge-active' : 'badge-inactive' ?>">
                        <?= $active ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">Generated</td>
                <td class="value" style="color:#718096; font-weight:normal;">
                    <?= date('d F Y, H:i') ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- metrics table -->
    <div class="metrics-section">
        <div class="section-title">Metrics</div>

        <?php if (!empty($ad->metrics)): ?>
        <table class="metrics-table">
            <thead>
                <tr>
                    <th>Metric Name</th>
                    <th class="text-right">Value</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ad->metrics as $metric): ?>
                <tr>
                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $metric->metric_name))) ?></td>
                    <td class="metric-value">
                        <?= number_format((float)$metric->metric_value, 2, ',', '.') ?>
                    </td>
                    <td class="metric-date">
                        <?= date('d M Y H:i', strtotime($metric->updated_at)) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="no-metrics">No metric data available for this ad.</p>
        <?php endif; ?>
    </div>

    <!-- footer -->
    <div class="page-footer">
        Ads Aggregator &mdash; Confidential &mdash; <?= date('Y') ?>
    </div>

</body>
</html>

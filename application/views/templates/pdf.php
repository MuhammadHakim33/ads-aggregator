<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Campaign Report – <?= $campaign->name ?? 'N/A' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #2c2c2c;
            background: #ffffff;
        }

        .page-header {
            background-color: #1a2332;
            padding: 18px 24px;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .page-header .report-label {
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
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
            font-size: 12px;
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
            color: #383f4aff;
            font-size: 12px;
        }

        .info-grid .value {
            font-weight: bold;
            font-size: 12px;
            color: #1a202c;
        }

        .info-grid tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
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
            page-break-inside: avoid;
        }

        .metrics-section .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4a5568;
            margin: 12px 0px;
            border-bottom: 2px solid #1a2332;
            padding-bottom: 4px;
        }

        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .metrics-table thead tr {
            background-color: #1a2332;
            color: #ffffff;
        }

        .metrics-table thead th {
            padding: 9px 12px;
            text-align: left;
            font-size: 12px;
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
            font-size: 12px;
        }

        .no-metrics {
            text-align: center;
            color: #a0aec0;
            font-style: italic;
            padding: 20px;
        }

        .page-footer {
            margin: 24px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: right;
            font-size: 12px;
            color: #a0aec0;
        }
    </style>
</head>

<body>

    <!-- header -->
    <div class="page-header">
        <div class="report-label">Campaign Report</div>
    </div>

    <!-- card -->
    <div class="info-card">
        <div class="card-title">Campaign Information</div>
        <table class="info-grid">
            <tr>
                <td class="label">Campaign Name</td>
                <td class="value"><?= ucwords($campaign->name ?? '-') ?></td>
            </tr>
            <?php if (!empty($campaign->description)): ?>
                <tr>
                    <td class="label">Description</td>
                    <td class="value" style="font-weight: normal;"><?= $campaign->description ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Contract Number</td>
                <td class="value"><?= $campaign->contract_number ?? '-' ?></td>
            </tr>
            <tr>
                <td class="label">Client</td>
                <td class="value"><?= ucwords($campaign->client_name ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">PIC</td>
                <td class="value"><?= $campaign->client_pic ?? '-' ?></td>
            </tr>
            <tr>
                <td class="label">Campaign Schedule</td>
                <td class="value">
                    <?= date('d M Y', strtotime($campaign->start_date)) ?> &ndash;
                    <?= date('d M Y', strtotime($campaign->end_date)) ?>
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

    <!-- ads list with metrics -->
    <?php if (!empty($campaign->ads)): ?>
        <?php foreach ($campaign->ads as $ad): ?>
            <div class="metrics-section">
                <div class="section-title">Ad: <?= $ad->title ?? '-' ?></div>
                <table class="info-grid" style="margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <tr>
                        <td class="label" style="width: 100px; padding: 5px 10px;">Platform:</td>
                        <td class="value" style="width: 100%; padding: 5px 10px;">
                            <?= ucfirst($ad->platform ?? '-') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label" style="width: 100px; padding: 5px 10px;">Content ID:</td>
                        <td class="value" style="width: 100%; padding: 5px 10px; font-family: monospace; font-size: 12px;">
                            <?= $ad->content_identifier ?? '-' ?>
                        </td>
                    </tr>
                </table>

                <?php if (!empty($ad->metrics)): ?>
                    <table class="metrics-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Metric Name</th>
                                <th class="text-right" style="width: 25%;">Value</th>
                                <th style="width: 25%;">Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ad->metrics as $metric): ?>
                                <tr>
                                    <td><?= ucwords(str_replace('_', ' ', $metric->metric_name)) ?></td>
                                    <td class="metric-value">
                                        <?= number_format((float) $metric->metric_value, 0, ',', '.') ?>
                                    </td>
                                    <td class="metric-date">
                                        <?= date('d M Y H:i', strtotime($metric->updated_at)) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-metrics" style="border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px;">No metric data
                        available for this ad.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="metrics-section">
            <p class="no-metrics" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px;">No ads connected to
                this campaign.</p>
        </div>
    <?php endif; ?>

    <!-- footer -->
    <div class="page-footer">
        Ads Aggregator &mdash; Campaign Export Report &mdash; <?= date('Y') ?>
    </div>

</body>

</html>
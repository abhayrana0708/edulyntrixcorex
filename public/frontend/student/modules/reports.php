<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../../../includes/db_connect.php';

$sid = (string)($_SESSION['student_id'] ?? '');

try {
    // 1. Calculate SGPA (Join grades with subjects for credits)
    $stmt_gpa = $pdo->prepare("
        SELECT g.semester, 
               SUM(s.credits * g.grade_point) / SUM(s.credits) as sgpa 
        FROM grades g
        JOIN subjects s ON g.subject_code = s.subject_code
        WHERE g.student_id = :sid 
        GROUP BY g.semester 
        ORDER BY g.semester ASC
    ");
    $stmt_gpa->execute([':sid' => $sid]);
    $gpa_data = $stmt_gpa->fetchAll();

    // 2. Fetch Detailed Grade Sheet
    $stmt_res = $pdo->prepare("
        SELECT g.*, s.subject_name, s.credits 
        FROM grades g 
        JOIN subjects s ON g.subject_code = s.subject_code 
        WHERE g.student_id = :sid 
        ORDER BY g.semester DESC, s.subject_name ASC
    ");
    $stmt_res->execute([':sid' => $sid]);
    $results = $stmt_res->fetchAll();

    // 3. Calculate Overall CGPA
    $stmt_cgpa = $pdo->prepare("
        SELECT SUM(s.credits * g.grade_point) / SUM(s.credits) as cgpa 
        FROM grades g
        JOIN subjects s ON g.subject_code = s.subject_code
        WHERE g.student_id = :sid
    ");
    $stmt_cgpa->execute([':sid' => $sid]);
    $cgpa_val = $stmt_cgpa->fetchColumn();

} catch (PDOException $e) {
    error_log("Report Error: " . $e->getMessage());
    die("<div style='padding:20px; color:#ef4444;'>Nexus Sync Failure.</div>");
}
?>

<div class="module-entrance" style="animation: nexusFadeIn 0.5s ease-out forwards;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
        <div>
            <h2 style="color: #1e293b; font-weight: 800; margin: 0; font-size: 1.8rem;">Academic <b>Transcript</b></h2>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 5px;">Validated records for ID: <b><?= htmlspecialchars($sid) ?></b></p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Cumulative GPA</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #3b82f6; line-height: 1;"><?= number_format($cgpa_val ?? 0, 2) ?></div>
        </div>
    </div>

    <div style="background: white; padding: 25px; border-radius: 24px; border: 1px solid #e2e8f0; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <h3 style="color: #1e293b; font-size: 0.9rem; font-weight: 800; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px;">GPA <b>Progression Trend</b></h3>
        <div style="height: 250px; width: 100%;">
            <canvas id="gpaChart"></canvas>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 35px;">
        <?php foreach($gpa_data as $row): ?>
            <div style="background: white; padding: 25px; border-radius: 24px; border: 1px solid #e2e8f0; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #3b82f6;"></div>
                <small style="color: #94a3b8; font-weight: 800; text-transform: uppercase; font-size: 0.6rem; letter-spacing: 1px;">Semester <?= $row['semester'] ?></small>
                <div style="font-size: 1.6rem; font-weight: 800; color: #1e293b; margin-top: 8px;"><?= number_format($row['sgpa'], 2) ?></div>
                <div style="display: flex; align-items: center; gap: 5px; margin-top: 10px; color: #10b981; font-size: 0.75rem; font-weight: 700;">
                    <i class="fas fa-check-circle"></i> VERIFIED
                </div>
            </div>
        <?php endforeach; ?>
    </div>

   <div style="background: white; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <th style="padding: 20px; font-size: 0.75rem; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 1px;">Subject</th>
                <th style="padding: 20px; font-size: 0.75rem; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Credits</th>
                <th style="padding: 20px; font-size: 0.75rem; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Sem</th>
                <th style="padding: 20px; font-size: 0.75rem; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Grade</th>
                <th style="padding: 20px; font-size: 0.75rem; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($results)): ?>
                <tr><td colspan="5" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 600;">No academic records found in Nexus Core.</td></tr>
            <?php endif; ?>

            <?php foreach($results as $res): ?>
            <tr style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                <td style="padding: 20px;">
                    <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;"><?= htmlspecialchars($res['subject_name']) ?></div>
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 3px;"><?= htmlspecialchars($res['subject_code']) ?></div>
                </td>

                <td style="padding: 20px; text-align: center; font-weight: 700; color: #475569;">
                    <?= $res['credits'] ?>
                </td>

                <td style="padding: 20px; text-align: center; font-weight: 700; color: #475569;">
                    <?= $res['semester'] ?>
                </td>

                <td style="padding: 20px; text-align: center;">
                    <span style="background: #eff6ff; color: #3b82f6; padding: 6px 14px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; border: 1px solid #dbeafe;">
                        <?= htmlspecialchars($res['grade']) ?>
                    </span>
                </td>

                <td style="padding: 20px; text-align: center;">
                    <?php if($res['grade_point'] >= 4.00): ?>
                        <span style="color: #10b981; font-weight: 800; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; gap: 5px;">
                            <i class="fas fa-check-circle"></i> CLEAR
                        </span>
                    <?php else: ?>
                        <span style="color: #ef4444; font-weight: 800; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; gap: 5px;">
                            <i class="fas fa-exclamation-triangle"></i> ARREAR
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    (function() {
        // Function to build the chart
        const buildChart = () => {
            const canvas = document.getElementById('gpaChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            
            // Data extracted from PHP
            const labels = [<?php echo '"' . implode('","', array_column($gpa_data, 'semester')) . '"'; ?>].map(s => 'Sem ' + s);
            const dataPoints = [<?php echo implode(',', array_column($gpa_data, 'sgpa')); ?>];

            if (dataPoints.length === 0) return;

            // Destroy existing chart instance if it exists to prevent overlap
            if (window.myGpaChart) { window.myGpaChart.destroy(); }

            window.myGpaChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'GPA',
                        data: dataPoints,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#3b82f6',
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 10, ticks: { stepSize: 2 } },
                        x: { grid: { display: false } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        };

        // Use a short timeout to ensure the DOM has finished injecting the canvas
        setTimeout(buildChart, 200);
    })();
</script>

<style>
@keyframes nexusFadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@media print { .sidebar, .top-nav, button, canvas { display: none !important; } .main-content { margin: 0 !important; padding: 0 !important; } }
</style>
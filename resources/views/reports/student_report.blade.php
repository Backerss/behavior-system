{{-- filepath: c:\Users\AsanR\OneDrive\Desktop\วิจัยแก้ม\behavior-system\resources\views\reports\student_report.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>รายงานพฤติกรรมและความประพฤตินักเรียน</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12pt;
            line-height: 1.3;
            margin: 15px;
            padding: 0;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .logo {
            margin-bottom: 10px;
        }
        
        .logo img {
            width: 80px;
            height: 80px;
            margin: 0 auto;
        }
        
        .school-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .school-address {
            font-size: 11pt;
            margin-bottom: 5px;
            color: #333;
        }
        
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 10px;
            text-decoration: underline;
        }
        
        .report-number {
            font-size: 11pt;
            margin-top: 5px;
            color: #666;
        }
        
        .two-column {
            width: 100%;
        }
        
        .left-side {
            width: 58%;
            float: left;
            margin-right: 2%;
        }
        
        .right-side {
            width: 40%;
            float: right;
        }
        
        .section {
            margin-bottom: 15px;
        }
        
        .section-header {
            font-size: 14pt;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #1020AD;
        }
        
        .info-line {
            margin-bottom: 5px;
            font-size: 11pt;
        }
        
        .label {
            font-weight: bold;
            width: 100px;
            display: inline-block;
        }
        
        .value {
            border-bottom: 1px dotted #666;
            display: inline-block;
            min-width: 150px;
            padding-bottom: 2px;
        }
        
        .score-box {
            background-color: #f8f9fa;
            border: 2px solid #1020AD;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .score-header {
            font-size: 14pt;
            font-weight: bold;
            color: #1020AD;
            margin-bottom: 8px;
        }
        
        .score-number {
            font-size: 24pt;
            font-weight: bold;
            color: #1020AD;
        }
        
        .score-text {
            font-size: 12pt;
            margin-top: 8px;
            padding: 5px 10px;
        }
        
        .excellent { background-color: #d4edda; color: #155724; }
        .good { background-color: #d1ecf1; color: #0c5460; }
        .fair { background-color: #fff3cd; color: #856404; }
        .poor { background-color: #f8d7da; color: #721c24; }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10pt;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        
        .data-table th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }
        
        .center { text-align: center; }
        
        .summary-box {
            border: 1px solid #6c757d;
            padding: 10px;
            margin: 10px 0;
            background-color: #f8f9fa;
            font-size: 11pt;
        }
        
        .summary-header {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 8px;
            color: #495057;
        }
        
        .summary-line {
            margin-bottom: 3px;
        }
        
        .summary-label {
            font-weight: bold;
            width: 100px;
            display: inline-block;
        }
        
        .teacher-info-box {
            border: 1px solid #1020AD;
            padding: 10px;
            margin: 10px 0;
            background-color: #f8f9fa;
            font-size: 11pt;
        }
        
        .teacher-header {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 8px;
            color: #1020AD;
        }
        
        .signatures {
            margin-top: 30px;
            font-size: 11pt;
            clear: both;
        }
        
        .sig-table {
            width: 100%;
        }
        
        .sig-table td {
            text-align: center;
            padding: 15px 5px;
            width: 33.33%;
            vertical-align: top;
        }
        
        .sig-line {
            border-bottom: 1px solid #000;
            width: 100px;
            margin: 20px auto 8px;
        }
        
        .no-records {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 15px;
        }
        
        .footer {
            font-size: 9pt;
            color: #666;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            text-align: center;
            clear: both;
        }
        
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo โรงเรียน" style="width: 80px; height: 80px;">
        </div>
        <div class="school-name">โรงเรียนนวมินทราชูทิศ มัชฌิม</div>
        <div class="school-address">เบอร์โทรศัพท์ 0-5622-6967, 0-5622-7771, 0-5622-5768</div>
        <div class="report-title">รายงานพฤติกรรมและความประพฤตินักเรียน</div>
        <div class="report-number">เลขที่: {{ 'SR-' . date('Y') . '-' . str_pad($student->id ?? 1, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="two-column">
        <!-- Left Side -->
        <div class="left-side">
            <!-- Student Info -->
            <div class="section">
                <div class="section-header">ข้อมูลนักเรียน</div>
                
                <div class="info-line">
                    <span class="label">ชื่อ-นามสกุล:</span>
                    <span class="value">
                        @if($student->user)
                            {{ $student->user->users_name_prefix ?? '' }}{{ $student->user->users_first_name ?? '' }} {{ $student->user->users_last_name ?? '' }}
                        @else
                            ไม่มีข้อมูล
                        @endif
                    </span>
                </div>
                
                <div class="info-line">
                    <span class="label">รหัสนักเรียน:</span>
                    <span class="value">{{ $student->students_student_code ?? 'ไม่มีข้อมูล' }}</span>
                </div>
                
                <div class="info-line">
                    <span class="label">ชั้น/ห้อง:</span>
                    <span class="value">
                        @if($student->classroom)
                            {{ $student->classroom->classes_level ?? '' }}/{{ $student->classroom->classes_room_number ?? '' }}
                        @else
                            ไม่มีข้อมูล
                        @endif
                    </span>
                </div>
                
                <div class="info-line">
                    <span class="label">ปีการศึกษา:</span>
                    <span class="value">{{ $student->students_academic_year ?? date('Y') }}</span>
                </div>
                
                <div class="info-line">
                    <span class="label">เพศ:</span>
                    <span class="value">
                        @if($student->students_gender == 'male')
                            ชาย
                        @elseif($student->students_gender == 'female')
                            หญิง
                        @else
                            ไม่ระบุ
                        @endif
                    </span>
                </div>
            </div>

            <!-- Guardian Info -->
            <div class="section">
                <div class="section-header">ข้อมูลผู้ปกครอง</div>
                
                <div class="info-line">
                    <span class="label">ชื่อผู้ปกครอง:</span>
                    <span class="value">
                        @if(isset($student->guardian) && $student->guardian && $student->guardian->user)
                            {{ $student->guardian->user->users_name_prefix ?? '' }}{{ $student->guardian->user->users_first_name ?? '' }} {{ $student->guardian->user->users_last_name ?? '' }}
                        @else
                            ไม่มีข้อมูล
                        @endif
                    </span>
                </div>
                
                <div class="info-line">
                    <span class="label">เบอร์โทร:</span>
                    <span class="value">
                        @if(isset($student->guardian) && $student->guardian)
                            {{ $student->guardian->guardians_phone ?? 'ไม่มีข้อมูล' }}
                        @else
                            ไม่มีข้อมูล
                        @endif
                    </span>
                </div>
            </div>

            <!-- Behavior Records -->
            <div class="section">
                <div class="section-header">ประวัติการบันทึกพฤติกรรม</div>
                
                @if(isset($student->behaviorReports) && count($student->behaviorReports) > 0)
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="8%">ลำดับ</th>
                                <th width="18%">วันที่</th>
                                <th width="52%">ประเภทพฤติกรรม</th>
                                <th width="22%">คะแนนหัก</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->behaviorReports->take(4) as $index => $report)
                                <tr>
                                    <td class="center">{{ $index + 1 }}</td>
                                    <td class="center">
                                        @if($report->reports_report_date)
                                            @php
                                            try {
                                                echo \Carbon\Carbon::parse($report->reports_report_date)->format('d/m/y');
                                            } catch(\Exception $e) {
                                                echo '-';
                                            }
                                            @endphp
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $report->violation ? mb_substr($report->violation->violations_name, 0, 28) : 'ไม่มีข้อมูล' }}</td>
                                    <td class="center">{{ $report->reports_points_deducted ?? ($report->violation ? $report->violation->violations_points_deducted : 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(count($student->behaviorReports) > 4)
                        <div style="font-size: 10pt; text-align: center; color: #666;">
                            แสดงข้อมูล 4 รายการล่าสุด (ทั้งหมด {{ count($student->behaviorReports) }} รายการ)
                        </div>
                    @endif
                @else
                    <div class="no-records">ไม่พบประวัติการบันทึกพฤติกรรม</div>
                @endif
            </div>
        </div>

        <!-- Right Side -->
        <div class="right-side">
            <!-- Score -->
            @php
                $currentScore = $student->students_current_score ?? 100;
                $scoreStatus = '';
                $scoreClass = '';
                
                if ($currentScore >= 90) {
                    $scoreStatus = 'ดีเยี่ยม';
                    $scoreClass = 'excellent';
                } elseif ($currentScore >= 80) {
                    $scoreStatus = 'ดี';
                    $scoreClass = 'good';
                } elseif ($currentScore >= 70) {
                    $scoreStatus = 'พอใช้';
                    $scoreClass = 'fair';
                } else {
                    $scoreStatus = 'ต้องปรับปรุง';
                    $scoreClass = 'poor';
                }
            @endphp

            <div class="score-box">
                <div class="score-header">คะแนนความประพฤติ</div>
                <div class="score-number">{{ $currentScore }}/100</div>
                <div class="score-text {{ $scoreClass }}">{{ $scoreStatus }}</div>
            </div>

            <!-- Teacher Info -->
            <div class="teacher-info-box">
                <div class="teacher-header">ข้อมูลครูประจำชั้น</div>
                <div class="summary-line">
                    <span class="summary-label">ชื่อครู:</span>
                    <span>
                        @if($student->classroom && $student->classroom->teacher && $student->classroom->teacher->user)
                            {{ $student->classroom->teacher->user->users_name_prefix ?? '' }}{{ $student->classroom->teacher->user->users_first_name ?? '' }} {{ $student->classroom->teacher->user->users_last_name ?? '' }}
                        @else
                            ไม่มีข้อมูล
                        @endif
                    </span>
                </div>
                <div class="summary-line">
                    <span class="summary-label">เบอร์โทร:</span>
                    <span>
                        @if($student->classroom && $student->classroom->teacher)
                            {{ $student->classroom->teacher->teachers_phone ?? 'ไม่มีข้อมูล' }}
                        @else
                            ไม่มีข้อมูล
                        @endif
                    </span>
                </div>
            </div>

            <!-- Summary -->
            @php
                $categories = ['light' => 0, 'medium' => 0, 'severe' => 0];
                $totalPoints = 0;
                
                if(isset($student->behaviorReports)) {
                    foreach($student->behaviorReports as $report) {
                        if($report->violation) {
                            $category = $report->violation->violations_category ?? 'light';
                            $categories[$category] = ($categories[$category] ?? 0) + 1;
                            $totalPoints += $report->reports_points_deducted ?? ($report->violation->violations_points_deducted ?? 0);
                        }
                    }
                }
            @endphp

            <div class="summary-box">
                <div class="summary-header">สรุปพฤติกรรม</div>
                <div class="summary-line">
                    <span class="summary-label">ทั้งหมด:</span>
                    <span>{{ count($student->behaviorReports ?? []) }} ครั้ง</span>
                </div>
                <div class="summary-line">
                    <span class="summary-label">ระดับเบา:</span>
                    <span>{{ $categories['light'] ?? 0 }} ครั้ง</span>
                </div>
                <div class="summary-line">
                    <span class="summary-label">ระดับกลาง:</span>
                    <span>{{ $categories['medium'] ?? 0 }} ครั้ง</span>
                </div>
                <div class="summary-line">
                    <span class="summary-label">ระดับรุนแรง:</span>
                    <span>{{ $categories['severe'] ?? 0 }} ครั้ง</span>
                </div>
                <div class="summary-line">
                    <span class="summary-label">คะแนนหักรวม:</span>
                    <span>{{ $totalPoints }} คะแนน</span>
                </div>
            </div>
        </div>
    </div>

    <div class="clear"></div>

    <!-- Signatures -->
    <div class="signatures">
        <table class="sig-table">
            <tr>
                <td>
                    <div style="margin-bottom: 40px;">
                        <div style="margin-top: 20px;">
                            ลงชื่อ................................................
                        </div>
                        <div style="font-size: 9pt; margin-top: 5px;">
                            @if($student->classroom && $student->classroom->teacher && $student->classroom->teacher->user)
                                ({{ $student->classroom->teacher->user->users_name_prefix ?? '' }}{{ $student->classroom->teacher->user->users_first_name ?? '' }} {{ $student->classroom->teacher->user->users_last_name ?? '' }})
                            @else
                                (..................................)
                            @endif
                        </div>
                        <div style="font-size: 9pt; margin-top: 3px;">
                            ครูประจำชั้น
                        </div>
                    </div>
                </td>
                <td>
                    <div style="margin-bottom: 40px;">
                        <div style="margin-top: 20px;">
                            ลงชื่อ................................................
                        </div>
                        <div style="font-size: 9pt; margin-top: 5px;">
                            (................................................)
                        </div>
                        <div style="font-size: 9pt; margin-top: 3px;">
                            หัวหน้าระดับชั้น
                        </div>
                    </div>
                </td>
                <td>
                    <div style="margin-bottom: 40px;">
                        <div style="margin-top: 20px;">
                            ลงชื่อ................................................
                        </div>
                        <div style="font-size: 9pt; margin-top: 5px;">
                            (นายพงษ์เทพ เจริญไทย)
                        </div>
                        <div style="font-size: 9pt; margin-top: 3px;">
                            ผู้อำนวยการสถานศึกษา
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        พิมพ์: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} | 
        โดย: {{ auth()->user()->users_first_name ?? 'ระบบ' }} {{ auth()->user()->users_last_name ?? '' }}
    </div>
</body>
</html>
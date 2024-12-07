
<div class="container mt-4">
    <h2>List of surveys</h2>
    <form id="surveyForm" method="POST">
        <table class="table table-striped table-bordered">
            <thead class="table-dark table-hover">
                <tr>
                    <th><input type="checkbox" class="form-check-input" id="selectAll" checked></th>
                    <th>Site</th>
                    <th>Survey</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $data['surveys'];
                if (isset($result) && !empty($result)) {
                    foreach ($result as $item): ?>
                        <tr>
                            <td>
                                <input type="checkbox"
                                    name="survey_id[]"
                                    class="form-check-input"
                                    value="<?php echo htmlspecialchars($item['survey_id'] ?? ''); ?>"
                                    checked>
                            </td>
                            <td><?php echo htmlspecialchars($item['site'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($item['name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($item['date_captured'] ?? ''); ?></td>
                            <td class="d-none"><?php echo htmlspecialchars($item['organization_id'] ?? ''); ?></td>
                            <td class="d-none"><?php echo htmlspecialchars($item['site_id'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach;
                } else { ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucune donnée disponible</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="row mt-3 mb-4 dowmloadButton">
            <div class="col-md-2">
                <button type="submit" action="/surveyList/dowmload" class="btn btn-success" id="downloadBtn">
                    <i class="bi bi-download"></i> Download
                </button>
            </div>
        </div>       
    </form>
</div>


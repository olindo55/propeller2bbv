
<div class="container mt-1">
        <!-- Section selector of SL, date and button -->
        <form method="POST" action="/homepage/compare" class="d-flex flex-column mt-3 mb-4 p-2 bg-light shadow-sm">
            <div class="row mb-3">
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">SubLot</span>
                    <select class="form-select" id="subLotSelect" name="sublot_id">
                        <?php
                        $organizations = $data['sublot'];
                        foreach ($organizations['results'] as $organization): ?>
                            <option value="<?php echo htmlspecialchars($organization['id'] ?? ''); ?>">
                                <?php echo htmlspecialchars($organization['name'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">From</span>
                    <input type="date" class="form-control" id="dateFromInput" name="from_date" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">To</span>
                    <input type="date" class="form-control" id="dateToInput" name="to_date" required>
                </div>
            </div>
            <div class="col-md-2 d-flex mb-3">
                <button type="submit" class="btn btn-primary" id="compareBtn">Compare</button>
            </div>
        </form>
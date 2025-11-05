<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chat System Test</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Test Message Sending</h6>
                            <div class="mb-3">
                                <label for="receiverId" class="form-label">Receiver ID:</label>
                                <input type="number" id="receiverId" class="form-control" value="1" min="1">
                            </div>
                            <div class="mb-3">
                                <label for="testMessage" class="form-label">Test Message:</label>
                                <input type="text" id="testMessage" class="form-control" value="Hello, this is a test message!">
                            </div>
                            <button id="sendTestMessage" class="btn btn-primary">Send Test Message</button>
                        </div>
                        <div class="col-md-6">
                            <h6>System Tests</h6>
                            <button id="testDb" class="btn btn-info mb-2">Test Database</button><br>
                            <button id="testDebug" class="btn btn-warning mb-2">Debug Session</button><br>
                            <button id="getMessages" class="btn btn-success mb-2">Get Recent Messages</button>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Test Results</h6>
                            <div id="testResults" class="alert alert-info">
                                Click a test button above to see results here...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const testResults = document.getElementById('testResults');
    
    function showResult(data, type = 'info') {
        const alertClass = type === 'error' ? 'alert-danger' : 
                          type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        testResults.className = `alert ${alertClass}`;
        testResults.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
    }
    
    // Test message sending
    document.getElementById('sendTestMessage').addEventListener('click', function() {
        const receiverId = document.getElementById('receiverId').value;
        const message = document.getElementById('testMessage').value;
        
        fetch('<?= base_url('chat-test/test-send') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `receiver_id=${receiverId}&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            showResult(data, data.success ? 'success' : 'error');
        })
        .catch(error => {
            showResult({ error: 'Network error', message: error.message }, 'error');
        });
    });
    
    // Test database
    document.getElementById('testDb').addEventListener('click', function() {
        fetch('<?= base_url('chat-test/test-db') ?>')
        .then(response => response.json())
        .then(data => {
            showResult(data, data.success ? 'success' : 'error');
        })
        .catch(error => {
            showResult({ error: 'Network error', message: error.message }, 'error');
        });
    });
    
    // Debug session
    document.getElementById('testDebug').addEventListener('click', function() {
        fetch('<?= base_url('chat-test/debug') ?>')
        .then(response => response.json())
        .then(data => {
            showResult(data, 'info');
        })
        .catch(error => {
            showResult({ error: 'Network error', message: error.message }, 'error');
        });
    });
    
    // Get recent messages
    document.getElementById('getMessages').addEventListener('click', function() {
        fetch('<?= base_url('chat-test/get-recent-messages') ?>')
        .then(response => response.json())
        .then(data => {
            showResult(data, data.success ? 'success' : 'error');
        })
        .catch(error => {
            showResult({ error: 'Network error', message: error.message }, 'error');
        });
    });
});
</script>
<?= $this->endSection() ?>


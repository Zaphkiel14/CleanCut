<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
	<div class="row">
		<div class="col-12 col-lg-10 col-xl-8 mx-auto">
			<div class="d-flex justify-content-between align-items-center mb-4">
				<h1 class="h3 mb-0">
					<i class="fas fa-eye"></i> <?= htmlspecialchars($post['title'] ?? 'Post') ?>
				</h1>
				<a href="<?= base_url('social-feed') ?>" class="btn btn-outline-secondary">
					<i class="fas fa-arrow-left"></i> Back to Feed
				</a>
			</div>

			<div class="card">
				<div class="card-header bg-transparent">
					<div class="d-flex align-items-center">
						<div class="flex-shrink-0 me-3">
							<?php if (!empty($post['profile_picture'])): ?>
								<?php 
									$pp = $post['profile_picture'];
									$ppResolved = (is_file(FCPATH . $pp)) ? base_url($pp) : (is_file(FCPATH . 'writable/' . $pp) ? base_url('file/writable?path=' . rawurlencode($pp)) : base_url($pp));
								?>
								<img src="<?= $ppResolved ?>" class="rounded-circle" width="48" height="48" alt="Profile">
							<?php else: ?>
								<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
									<i class="fas fa-user text-white"></i>
								</div>
							<?php endif; ?>
						</div>
						<div class="flex-grow-1">
							<h6 class="mb-0"><?= htmlspecialchars(($post['first_name'] ?? 'User') . ' ' . ($post['last_name'] ?? '')) ?></h6>
							<small class="text-muted">
								<?= isset($post['created_at']) ? date('M d, Y', strtotime($post['created_at'])) : '' ?>
								<?php if (($post['post_type'] ?? '') === 'work_showcase'): ?>
									<span class="badge bg-primary ms-2">Work Showcase</span>
								<?php elseif (($post['post_type'] ?? '') === 'status_update'): ?>
									<span class="badge bg-info ms-2">Status Update</span>
								<?php endif; ?>
							</small>
						</div>
					</div>
				</div>
				<div class="card-body">
					<?php if (!empty($post['content'])): ?>
						<p class="lead"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
					<?php endif; ?>

					<?php 
						$blobUrls = $post['image_urls'] ?? [];
						$legacyImages = [];
						if (empty($blobUrls) && !empty($post['images'])) {
							$legacyImages = json_decode($post['images'], true) ?: [];
						}
					?>
					<?php if (!empty($blobUrls) || !empty($legacyImages)): ?>
						<div class="mb-3">
							<?php if (!empty($blobUrls)): ?>
								<?php foreach ($blobUrls as $url): ?>
									<img src="<?= $url ?>" class="img-fluid rounded mb-2" alt="Post image">
								<?php endforeach; ?>
							<?php else: ?>
								<?php foreach ($legacyImages as $image): ?>
									<?php 
										$imgResolved = (is_file(FCPATH . $image)) ? base_url($image) : (is_file(FCPATH . 'writable/' . $image) ? base_url('file/writable?path=' . rawurlencode($image)) : base_url($image));
									?>
									<img src="<?= $imgResolved ?>" class="img-fluid rounded mb-2" alt="Post image">
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if (($post['status'] ?? null) && ($post['post_type'] ?? '') === 'status_update'): ?>
						<div class="mb-3">
							<span class="status-badge status-<?= $post['status'] ?>">
								<?= ucfirst(str_replace('_', ' ', $post['status'])) ?>
							</span>
						</div>
					<?php endif; ?>
				</div>
				<div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
					<div>
						<button class="btn btn-sm btn-outline-primary me-2" onclick="likePost(<?= (int)($post['post_id'] ?? 0) ?>)">
							<i class="fas fa-heart"></i>
							<span id="likes-<?= (int)($post['post_id'] ?? 0) ?>"><?= (int)($post['likes_count'] ?? 0) ?></span>
						</button>
						<button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleComments(<?= (int)($post['post_id'] ?? 0) ?>)">
							<i class="fas fa-comment"></i>
							<span id="comment-count-<?= (int)($post['post_id'] ?? 0) ?>"><?= (int)($post['comments_count'] ?? 0) ?></span>
						</button>
					</div>
					<div>
						<a href="<?= base_url('social-feed/user/' . (int)($post['user_id'] ?? 0)) ?>" class="text-muted text-decoration-none">
							<small>View Profile</small>
						</a>
					</div>
				</div>
				<div class="border-top p-3 d-none" id="comments-section-<?= (int)($post['post_id'] ?? 0) ?>">
					<div id="comments-list-<?= (int)($post['post_id'] ?? 0) ?>" class="mb-3"></div>
					<form class="d-flex gap-2" onsubmit="return submitComment(<?= (int)($post['post_id'] ?? 0) ?>, this)">
						<input type="text" name="content" class="form-control" placeholder="Write a comment..." required>
						<button class="btn btn-primary" type="submit">Post</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function toggleComments(postId) {
	const section = document.getElementById('comments-section-' + postId);
	if (!section) return;
	const isHidden = section.classList.contains('d-none');
	if (isHidden) {
		section.classList.remove('d-none');
		loadComments(postId);
	} else {
		section.classList.add('d-none');
	}
}

function loadComments(postId) {
	const listEl = document.getElementById('comments-list-' + postId);
	if (!listEl) return;
	listEl.innerHTML = '<div class="text-muted">Loading comments...</div>';
	fetch('<?= base_url('social-feed/post') ?>/' + postId + '/comments', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
		.then(r => r.json())
		.then(data => {
			if (!data.success) {
				listEl.innerHTML = '<div class="text-danger">Failed to load comments</div>';
				return;
			}
			if (!data.comments || data.comments.length === 0) {
				listEl.innerHTML = '<div class="text-muted">No comments yet.</div>';
				return;
			}
			listEl.innerHTML = data.comments.map(c => renderComment(c)).join('');
		})
		.catch(() => {
			listEl.innerHTML = '<div class="text-danger">Failed to load comments</div>';
		});
}

function submitComment(postId, formEl) {
	const input = formEl.querySelector('input[name="content"]');
	if (!input || !input.value.trim()) return false;
	const formData = new FormData();
	formData.append('content', input.value.trim());
	formData.append('csrf_test_name', document.querySelector('input[name="csrf_test_name"]')?.value || '');
	fetch('<?= base_url('social-feed/post') ?>/' + postId + '/comment', {
		method: 'POST',
		headers: { 'X-Requested-With': 'XMLHttpRequest' },
		credentials: 'same-origin',
		body: formData
	})
	.then(r => r.json())
	.then(data => {
		if (data.success) {
			input.value = '';
			loadComments(postId);
			const cc = document.getElementById('comment-count-' + postId);
			if (cc) cc.textContent = String(parseInt(cc.textContent || '0') + 1);
		} else {
			alert(data.error || 'Failed to add comment');
		}
	})
	.catch(() => alert('Failed to add comment'));
	return false;
}

function likePost(postId) {
	$.post('<?= base_url('social-feed/post') ?>/' + postId + '/like', function(response) {
		if (response.success) {
			const likesElement = $('#likes-' + postId);
			const currentLikes = parseInt(likesElement.text());
			likesElement.text(currentLikes + 1);
			const button = likesElement.closest('button');
			button.removeClass('btn-outline-primary').addClass('btn-primary');
			button.attr('onclick', 'unlikePost(' + postId + ')');
		} else {
			alert('Error: ' + response.error);
		}
	});
}
function unlikePost(postId) {
	$.post('<?= base_url('social-feed/post') ?>/' + postId + '/unlike', function(response) {
		if (response.success) {
			const likesElement = $('#likes-' + postId);
			const currentLikes = Math.max(0, parseInt(likesElement.text()) - 1);
			likesElement.text(currentLikes);
			const button = likesElement.closest('button');
			button.removeClass('btn-primary').addClass('btn-outline-primary');
			button.attr('onclick', 'likePost(' + postId + ')');
		} else {
			alert('Error: ' + response.error);
		}
	});
}

function renderComment(c) {
	const name = (c.first_name ? c.first_name : 'User') + (c.last_name ? ' ' + c.last_name : '');
	return `
	<div class="d-flex mb-2">
		<div class="flex-shrink-0 me-2">
			<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
				<i class="fas fa-user text-white" style="font-size: 0.75rem;"></i>
			</div>
		</div>
		<div class="flex-grow-1">
			<div class="bg-light rounded p-2">
				<strong>${name}</strong>
				<div>${escapeHtml(c.content || '')}</div>
			</div>
			<small class="text-muted">${c.created_at ? new Date(c.created_at).toLocaleString() : ''}</small>
		</div>
	</div>`;
}

function escapeHtml(str) {
	return String(str)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');
}
</script>
<?= $this->endSection() ?>



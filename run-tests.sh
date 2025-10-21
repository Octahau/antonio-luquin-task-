#!/bin/bash

echo "🚀 Running comprehensive test suite for the system..."
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to run tests and display results
run_test_suite() {
    local test_name="$1"
    local test_command="$2"
    local test_dir="$3"
    
    echo -e "\n${BLUE}Running $test_name...${NC}"
    echo "Command: $test_command"
    echo "Directory: $test_dir"
    echo "----------------------------------------"
    
    cd "$test_dir"
    
    if eval "$test_command"; then
        echo -e "${GREEN}✅ $test_name PASSED${NC}"
        return 0
    else
        echo -e "${RED}❌ $test_name FAILED${NC}"
        return 1
    fi
}

# Track overall results
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Backend Tests
echo -e "\n${YELLOW}=== BACKEND TESTS ===${NC}"

# Unit Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_suite "Backend Unit Tests" "php artisan test tests/Unit" "/workspace/backend"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi

# Feature Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_suite "Backend Feature Tests" "php artisan test tests/Feature" "/workspace/backend"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi

# All Backend Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_suite "All Backend Tests" "php artisan test" "/workspace/backend"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi

# Frontend Tests
echo -e "\n${YELLOW}=== FRONTEND TESTS ===${NC}"

# Frontend Unit Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_suite "Frontend Unit Tests" "npm run test:run" "/workspace/frontend"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi

# Frontend Component Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_suite "Frontend Component Tests" "npm run test:run src/test/components" "/workspace/frontend"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi

# Frontend Service Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_suite "Frontend Service Tests" "npm run test:run src/test/services" "/workspace/frontend"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi

# Frontend Store Tests
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if run_test_suite "Frontend Store Tests" "npm run test:run src/test/stores" "/workspace/frontend"; then
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi

# Summary
echo -e "\n${YELLOW}=== TEST SUMMARY ===${NC}"
echo "Total Test Suites: $TOTAL_TESTS"
echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}"
echo -e "Failed: ${RED}$FAILED_TESTS${NC}"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "\n${GREEN}🎉 All tests passed! The system is working correctly.${NC}"
    exit 0
else
    echo -e "\n${RED}⚠️  Some tests failed. Please check the output above for details.${NC}"
    exit 1
fi
